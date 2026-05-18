<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadFollowUp;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Services\AuditLogService;

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
        AuditLogService::created($lead, "New lead created: {$lead->customer_name}" . ($lead->phone ? " ({$lead->phone})" : ''));

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

        $oldValues = $lead->toArray();
        $lead->update([
            'status' => $validated['status'],
            'interest_level' => $validated['interest_level'],
            'customer_response' => $validated['customer_response'],
            'follow_up_date' => $validated['next_follow_up_date'] ?? $lead->follow_up_date,
        ]);
        AuditLogService::updated($lead, $oldValues, "Updated lead: {$lead->customer_name} → status: {$validated['status']}");

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
    
    /**
     * Overdue follow-ups dashboard — shows all leads whose follow_up_date has passed.
     */
    public function overdue(Request $request)
    {
        $query = Lead::with(['seller', 'followUps' => fn($q) => $q->latest()->limit(1)])
            ->where('status', 'pending');

        // Role-based restriction
        if (auth()->user()->role === 'saler') {
            $query->where('assigned_seller_id', auth()->id());
        }

        $filter = $request->get('filter', 'overdue');

        $query = match($filter) {
            'today'    => $query->whereDate('follow_up_date', today()),
            'upcoming' => $query->whereDate('follow_up_date', '>', today())
                                ->whereDate('follow_up_date', '<=', today()->addDays(7)),
            default    => $query->whereNotNull('follow_up_date')
                                ->where('follow_up_date', '<', today()),
        };

        // Seller filter (admin only)
        if (in_array(auth()->user()->role, ['admin', 'super_admin', 'accountant']) && $request->filled('saler_id')) {
            $query->where('assigned_seller_id', $request->saler_id);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('follow_up_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('follow_up_date', '<=', $request->date_to);
        }

        // Source filter
        if ($request->filled('source') && $request->source !== 'all') {
            $query->where('source', $request->source);
        }

        $leads   = $query->orderBy('follow_up_date')->paginate(30)->withQueryString();
        $sellers = User::whereIn('role', ['saler', 'admin', 'super_admin'])->get();

        // Summary counts (scoped by seller if saler)
        $baseQuery = Lead::where('status', 'pending');
        if (auth()->user()->role === 'saler') {
            $baseQuery->where('assigned_seller_id', auth()->id());
        }
        $overdueCount  = (clone $baseQuery)->where('follow_up_date', '<', today())->count();
        $todayCount    = (clone $baseQuery)->whereDate('follow_up_date', today())->count();
        $upcomingCount = (clone $baseQuery)->whereDate('follow_up_date', '>', today())
                            ->whereDate('follow_up_date', '<=', today()->addDays(7))->count();

        return view('admin.leads.overdue', compact(
            'leads', 'sellers', 'filter', 'overdueCount', 'todayCount', 'upcomingCount'
        ));
    }

    /**
     * Inline update of customer name / phone / notes / follow-up date.
     */
    public function quickEdit(Request $request, Lead $lead)
    {
        if (!in_array(auth()->user()->role, ['admin', 'super_admin']) && auth()->user()->id !== $lead->assigned_seller_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'customer_name'  => 'sometimes|required|string|max:255',
            'phone'          => 'sometimes|nullable|string|max:20',
            'email'          => 'sometimes|nullable|email|max:255',
            'source'         => 'sometimes|nullable|string|max:100',
            'priority'       => 'sometimes|in:low,normal,high,urgent',
            'follow_up_date' => 'sometimes|nullable|date',
            'promised_amount'     => 'sometimes|nullable|numeric|min:0',
            'promised_order_date' => 'sometimes|nullable|date',
        ]);

        $lead->update($validated);

        return response()->json(['success' => true, 'message' => 'Lead updated.']);
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

    // ── Follow-Up Data Center ───────────────────────────────────────────────

    public function followUpCenter(Request $request)
    {
        $user  = auth()->user();
        $query = Lead::with(['seller', 'followUps' => fn($q) => $q->latest()->limit(3)])
            ->where('status', 'pending');

        if ($user->role === 'saler') {
            $query->where('assigned_seller_id', $user->id);
        } elseif ($request->filled('saler_id')) {
            $query->where('assigned_seller_id', $request->saler_id);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('lead_type')) {
            $query->where('lead_type', $request->lead_type);
        }

        if ($request->filled('follow_up_status')) {
            match($request->follow_up_status) {
                'overdue'  => $query->overdue(),
                'today'    => $query->dueToday(),
                'upcoming' => $query->upcoming(),
                default    => null,
            };
        }

        $leads   = $query->orderByRaw("FIELD(priority,'urgent','high','normal','low')")
                         ->orderBy('follow_up_date')
                         ->paginate(20)
                         ->withQueryString();

        $sellers = User::whereIn('role', ['saler', 'admin', 'super_admin'])->get();

        // Summary counts for cards
        $baseQuery = Lead::where('status', 'pending');
        if ($user->role === 'saler') $baseQuery->where('assigned_seller_id', $user->id);

        $counts = [
            'total'    => (clone $baseQuery)->count(),
            'urgent'   => (clone $baseQuery)->whereIn('priority', ['urgent', 'high'])->count(),
            'promised' => (clone $baseQuery)->whereNotNull('promised_order_date')->count(),
            'overdue'  => (clone $baseQuery)->overdue()->count(),
        ];

        return view('admin.leads.follow-up-center', compact('leads', 'sellers', 'counts'));
    }

    public function updateFollowUp(Request $request, Lead $lead)
    {
        $user = auth()->user();
        if ($user->role === 'saler' && $user->id !== $lead->assigned_seller_id) {
            return back()->with('error', 'Unauthorized.');
        }

        $validated = $request->validate([
            'priority'            => 'required|in:low,normal,high,urgent',
            'lead_type'           => 'nullable|in:cold,warm,hot',
            'promised_amount'     => 'nullable|numeric|min:0',
            'promised_order_date' => 'nullable|date',
            'last_follow_up_notes'=> 'nullable|string|max:1000',
            'last_follow_up_date' => 'nullable|date',
        ]);

        $oldValues = $lead->toArray();
        $lead->update($validated);
        AuditLogService::updated($lead, $oldValues, "Follow-up updated for {$lead->customer_name}: priority {$validated['priority']}");

        return back()->with('success', "Follow-up data updated for {$lead->customer_name}.");
    }
}
