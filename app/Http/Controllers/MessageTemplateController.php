<?php

namespace App\Http\Controllers;

use App\Models\MessageTemplate;
use App\Models\Customer;
use App\Services\SmsApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageTemplateController extends Controller
{
    protected $smsService;

    public function __construct(SmsApiService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Display a listing of the templates.
     */
    public function index()
    {
        $templates = MessageTemplate::with('creator')->latest()->get();
        // Get customers for the "Send to Customer" modal
        $customers = Customer::orderBy('name')->get();
        return view('admin.message-templates.index', compact('templates', 'customers'));
    }

    /**
     * Store a newly created template.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'nullable|string|max:255',
        ]);

        MessageTemplate::create([
            'title' => $request->title,
            'content' => $request->content,
            'category' => $request->category,
            'created_by' => Auth::id(),
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Message template created successfully.');
    }

    /**
     * Update the specified template.
     */
    public function update(Request $request, $id)
    {
        $template = MessageTemplate::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'nullable|string|max:255',
        ]);

        $template->update($request->only(['title', 'content', 'category', 'is_active']));

        return redirect()->back()->with('success', 'Message template updated successfully.');
    }

    /**
     * Remove the specified template.
     */
    public function destroy($id)
    {
        $template = MessageTemplate::findOrFail($id);
        $template->delete();

        return redirect()->back()->with('success', 'Message template deleted successfully.');
    }

    /**
     * Send a template to one or multiple customers.
     */
    public function send(Request $request)
    {
        $request->validate([
            'recipient_type' => 'required|string|in:selected,assigned_leads,all_leads,all_customers',
            'template_id' => 'nullable|exists:message_templates,id',
            'custom_content' => 'nullable|string',
            'customer_ids' => 'required_if:recipient_type,selected|array',
            'customer_ids.*' => 'exists:customers,id',
        ]);

        $content = $request->custom_content;
        if (!$content && $request->template_id) {
            $template = MessageTemplate::findOrFail($request->template_id);
            $content = $template->content;
        }

        if (!$content) {
            return redirect()->back()->with('error', 'Message content is required.');
        }

        $customers = collect();

        if ($request->recipient_type === 'selected') {
            $customers = Customer::whereIn('id', $request->customer_ids)->get();
        } elseif ($request->recipient_type === 'assigned_leads') {
            // Get customers associated with leads assigned to current user
            // Since Lead doesn't link to Customer, we match by phone
            $leadPhones = \App\Models\Lead::where('assigned_seller_id', Auth::id())
                ->whereNotNull('phone')
                ->pluck('phone');
            $customers = Customer::whereIn('phone', $leadPhones)->get();
            // Also include leads without customer record? MessageTemplate requires Customer model for now.
            // Requirement says "send message to his customer".
        } elseif ($request->recipient_type === 'all_leads') {
             $leadPhones = \App\Models\Lead::whereNotNull('phone')->pluck('phone');
             $customers = Customer::whereIn('phone', $leadPhones)->get();
        } elseif ($request->recipient_type === 'all_customers') {
            $customers = Customer::whereNotNull('phone')->get();
        }

        if ($customers->isEmpty()) {
             return redirect()->back()->with('error', 'No valid recipients found.');
        }

        $successCount = 0;
        $failCount = 0;
        $errors = [];

        foreach ($customers as $customer) {
            if (!$customer->phone) {
                continue;
            }

            $result = $this->smsService->sendSMS($customer->phone, $content);

            if ($result['success']) {
                $successCount++;
            } else {
                $failCount++;
                $errors[] = "Failed for {$customer->name}: " . ($result['message'] ?? 'Unknown error');
            }
        }


        if ($failCount === 0) {
            return redirect()->back()->with('success', "Message sent successfully to {$successCount} customer(s).");
        }

        if ($successCount > 0) {
            return redirect()->back()->with('success', "Sent to {$successCount} customers. Failed for {$failCount} customers.")
                             ->with('error_list', $errors);
        }

        return redirect()->back()->with('error', "Failed to send messages.")
                         ->with('error_list', $errors);
    }
}
