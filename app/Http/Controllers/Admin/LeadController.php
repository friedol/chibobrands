<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadFollowUp;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $query = Lead::with(['seller', 'followUps.user']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('product_requested', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Interest Level filter
        if ($request->filled('interest_level') && $request->interest_level !== 'all') {
            $query->where('interest_level', $request->interest_level);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Role-based access and Salesperson filter
        if (auth()->user()->role === 'saler') {
            $query->where('assigned_seller_id', auth()->id());
        } elseif ($request->filled('saler_id') && $request->saler_id !== 'all') {
            $query->where('assigned_seller_id', $request->saler_id);
        }

        $leads = $query->latest()->paginate(25)->withQueryString();
        $sellers = User::whereIn('role', ['saler', 'admin', 'super_admin'])->get();
        $templates = \App\Models\MessageTemplate::where('is_active', true)->get();

        return view('admin.leads.index', compact('leads', 'sellers', 'templates'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->role === 'accountant') {
            return redirect()->back()->with('error', 'Accountants cannot create leads.');
        }

        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'product_requested' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:255',
            'follow_up_date' => 'nullable|date',
            'assigned_seller_id' => 'nullable|exists:users,id',
        ]);

        // Check if lead already exists for this phone
        if (!empty($validated['phone'])) {
            $existingLead = Lead::where('phone', $validated['phone'])->latest()->first();
            
            if ($existingLead) {
                // Add follow-up to existing lead
                $noteContent = "New inquiry/interaction logged.";
                if (!empty($validated['product_requested'])) {
                    $noteContent .= " Interested in: " . $validated['product_requested'];
                }

                $existingLead->followUps()->create([
                    'notes' => $noteContent,
                    'follow_up_date' => $validated['follow_up_date'],
                    'user_id' => auth()->id(),
                ]);

                // Update lead details if needed
                $updates = [];
                if ($validated['follow_up_date']) $updates['follow_up_date'] = $validated['follow_up_date'];
                if ($validated['product_requested'] && empty($existingLead->product_requested)) $updates['product_requested'] = $validated['product_requested'];
                if (!empty($updates)) $existingLead->update($updates);

                if ($validated['follow_up_date'] && $existingLead->assigned_seller_id) {
                    $seller = User::find($existingLead->assigned_seller_id);
                    if ($seller) {
                        try {
                            $seller->notify(new \App\Notifications\LeadFollowUpReminder($existingLead));
                        } catch (\Exception $e) {}
                    }
                }

                return redirect()->back()->with('success', 'Interaction logged to existing lead for ' . $existingLead->customer_name);
            }
        }

        $lead = Lead::create($validated);

        // Log creation in history
        $lead->followUps()->create([
            'notes' => "Lead created and assigned to " . ($lead->seller->name ?? 'Unassigned'),
            'user_id' => auth()->id(),
        ]);
        
        // If a follow up date is set and we have an assigned seller, notify them
        if ($lead->follow_up_date && $lead->assigned_seller_id) {
            $seller = User::find($lead->assigned_seller_id);
            if ($seller) {
                try {
                $seller->notify(new \App\Notifications\LeadFollowUpReminder($lead));
                } catch (\Exception $e) {}
            }
        }

        return redirect()->back()->with('success', 'Lead created successfully.');
    }

    public function update(Request $request, Lead $lead)
    {
        // Allow if admin, super_admin OR if it's the assigned seller
        if (!in_array(auth()->user()->role, ['admin', 'super_admin']) && auth()->user()->id !== $lead->assigned_seller_id) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,converted,not_interested',
            'interest_level' => 'nullable|string',
            'customer_response' => 'nullable|string',
            'follow_up_notes' => 'nullable|string',
            'next_follow_up_date' => 'nullable|date',
            'assigned_seller_id' => 'nullable|exists:users,id',
        ]);

        $lead->update([
            'status' => $validated['status'],
            'interest_level' => $validated['interest_level'],
            'customer_response' => $validated['customer_response'],
            'follow_up_date' => $validated['next_follow_up_date'] ?? $lead->follow_up_date,
        ]);

        if (array_key_exists('assigned_seller_id', $validated)) {
            $lead->update(['assigned_seller_id' => $validated['assigned_seller_id']]);
        }

        // Record the interaction if notes are provided, or if the status changed
        $notes = $validated['follow_up_notes'];
        if ($notes) {
            $lead->followUps()->create([
                'notes' => $notes,
                'follow_up_date' => $validated['next_follow_up_date'],
                'user_id' => auth()->id(),
            ]);
        } else {
            // Auto-log the status update if no manual notes were provided
            $lead->followUps()->create([
                'notes' => "Lead info updated: Status (" . strtoupper($validated['status']) . "), Interest (" . ($validated['interest_level'] ?? 'N/A') . ")",
                'user_id' => auth()->id(),
            ]);
        }

        // Notify if a new follow-up date is set
        if (isset($validated['next_follow_up_date']) && $lead->assigned_seller_id) {
            $seller = User::find($lead->assigned_seller_id);
            if ($seller) {
                $seller->notify(new \App\Notifications\LeadFollowUpReminder($lead));
            }
        }

        return redirect()->back()->with('success', 'Lead updated successfully.');
    }

    public function addFollowUp(Request $request, Lead $lead)
    {
        // Allow if admin, super_admin OR if it's the assigned seller
        if (!in_array(auth()->user()->role, ['admin', 'super_admin']) && auth()->user()->id !== $lead->assigned_seller_id) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $validated = $request->validate([
            'notes' => 'required|string',
            'follow_up_date' => 'nullable|date',
        ]);

        $lead->followUps()->create([
            'notes' => $validated['notes'],
            'follow_up_date' => $validated['follow_up_date'],
            'user_id' => auth()->id(),
        ]);
        
        if ($validated['follow_up_date']) {
            $lead->update(['follow_up_date' => $validated['follow_up_date']]);
            
            // Notify when follow up date is updated
            if ($lead->assigned_seller_id) {
                $seller = User::find($lead->assigned_seller_id);
                if ($seller) {
                    $seller->notify(new \App\Notifications\LeadFollowUpReminder($lead));
                }
            }
        }

    }
    
    public function print(Request $request)
    {
        $query = Lead::with(['seller', 'followUps.user']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('product_requested', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Interest Level filter
        if ($request->filled('interest_level') && $request->interest_level !== 'all') {
            $query->where('interest_level', $request->interest_level);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Role-based access and Salesperson filter
        if (auth()->user()->role === 'saler') {
            $query->where('assigned_seller_id', auth()->id());
        } elseif ($request->filled('saler_id') && $request->saler_id !== 'all') {
            $query->where('assigned_seller_id', $request->saler_id);
        }

        $leads = $query->latest()->get();

        return view('admin.leads.print-leads', compact('leads'));
    }
}
