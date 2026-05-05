<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Jobs\SendContactReplyEmail;

class ContactMessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = ContactMessage::with('repliedBy')->orderBy('created_at', 'desc');

        // Filter messages based on user role
        // Admin, super_admin, and manager can see all messages
        // Receptionist, designer, and operator can only see messages where contact email/phone matches their own
        if (in_array($user->role, ['receptionist', 'designer', 'operator'])) {
            $userEmail = $user->email ?? '';
            $userPhone = $user->phone ?? '';
            
            // Normalize phone numbers (remove spaces, dashes, etc. for comparison)
            $normalizePhone = function($phone) {
                if (empty($phone)) return '';
                return preg_replace('/[^\d+]/', '', $phone);
            };
            
            $normalizedUserPhone = $normalizePhone($userPhone);
            
            $query->where(function($q) use ($userEmail, $normalizedUserPhone) {
                // Match by email
                if (!empty($userEmail)) {
                    $q->where('email', $userEmail);
                }
                
                // Match by phone (using LIKE for partial matching after normalization)
                if (!empty($normalizedUserPhone)) {
                    // Remove all non-digit characters except + from phone for comparison
                    $q->orWhereRaw('REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, " ", ""), "-", ""), "(", ""), ")", ""), ".", "") LIKE ?', ['%' . $normalizedUserPhone . '%']);
                    // Also try exact match with normalized phone
                    $q->orWhere('phone', 'LIKE', '%' . $normalizedUserPhone . '%');
                }
            });
        }
        // Admin, super_admin, manager can see all messages (no additional filter)

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $messages = $query->paginate(20)->withQueryString();
        
        // Count new messages based on same filtering logic
        $newCountQuery = ContactMessage::where('status', 'new');
        if (in_array($user->role, ['receptionist', 'designer'])) {
            $userEmail = $user->email ?? '';
            $userPhone = $user->phone ?? '';
            $normalizePhone = function($phone) {
                if (empty($phone)) return '';
                return preg_replace('/[^\d+]/', '', $phone);
            };
            $normalizedUserPhone = $normalizePhone($userPhone);
            
            $newCountQuery->where(function($q) use ($userEmail, $normalizedUserPhone) {
                if (!empty($userEmail)) {
                    $q->where('email', $userEmail);
                }
                if (!empty($normalizedUserPhone)) {
                    $q->orWhereRaw('REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, " ", ""), "-", ""), "(", ""), ")", ""), ".", "") LIKE ?', ['%' . $normalizedUserPhone . '%']);
                    $q->orWhere('phone', 'LIKE', '%' . $normalizedUserPhone . '%');
                }
            });
        }
        $newCount = $newCountQuery->count();

        return view('admin.contact-messages.index', compact('messages', 'newCount'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = Auth::user();
        $message = ContactMessage::with('repliedBy')->findOrFail($id);
        
        // Check if user has access to this message
        if (in_array($user->role, ['receptionist', 'designer'])) {
            $userEmail = $user->email ?? '';
            $userPhone = $user->phone ?? '';
            
            // Normalize phone numbers for comparison
            $normalizePhone = function($phone) {
                if (empty($phone)) return '';
                return preg_replace('/[^\d+]/', '', $phone);
            };
            
            $normalizedUserPhone = $normalizePhone($userPhone);
            $normalizedMessagePhone = $normalizePhone($message->phone);
            
            // Check if email or phone matches
            $hasAccess = false;
            if (!empty($userEmail) && $message->email === $userEmail) {
                $hasAccess = true;
            }
            if (!empty($normalizedUserPhone) && !empty($normalizedMessagePhone) && 
                (strpos($normalizedMessagePhone, $normalizedUserPhone) !== false || 
                 strpos($normalizedUserPhone, $normalizedMessagePhone) !== false)) {
                $hasAccess = true;
            }
            
            if (!$hasAccess) {
                abort(403, 'You do not have access to this message.');
            }
        }
        
        // Mark as read if it's new
        if ($message->status === 'new') {
            $message->update(['status' => 'read']);
        }

        return view('admin.contact-messages.show', compact('message'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage (Reply to message)
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $request->validate([
            'admin_reply' => 'required|string|max:5000',
        ]);

        $message = ContactMessage::findOrFail($id);
        
        // Check if user has access to reply to this message
        if (in_array($user->role, ['receptionist', 'designer'])) {
            $userEmail = $user->email ?? '';
            $userPhone = $user->phone ?? '';
            
            // Normalize phone numbers for comparison
            $normalizePhone = function($phone) {
                if (empty($phone)) return '';
                return preg_replace('/[^\d+]/', '', $phone);
            };
            
            $normalizedUserPhone = $normalizePhone($userPhone);
            $normalizedMessagePhone = $normalizePhone($message->phone);
            
            // Check if email or phone matches
            $hasAccess = false;
            if (!empty($userEmail) && $message->email === $userEmail) {
                $hasAccess = true;
            }
            if (!empty($normalizedUserPhone) && !empty($normalizedMessagePhone) && 
                (strpos($normalizedMessagePhone, $normalizedUserPhone) !== false || 
                 strpos($normalizedUserPhone, $normalizedMessagePhone) !== false)) {
                $hasAccess = true;
            }
            
            if (!$hasAccess) {
                abort(403, 'You do not have permission to reply to this message.');
            }
        }
        
        $message->update([
            'admin_reply' => $request->admin_reply,
            'status' => 'replied',
            'replied_at' => now(),
            'replied_by' => Auth::id(),
        ]);

        // Optionally send email to customer
        // Mail::to($message->email)->send(new ContactReplyMail($message));

        return redirect()->route('admin.contact-messages.show', $message->id)
            ->with('success', 'Reply sent successfully!');
    }

    /**
     * Send email reply to customer (using queue for async processing)
     */
    public function sendEmailReply(Request $request, $id)
    {
        $user = Auth::user();
        $request->validate([
            'email_reply' => 'required|string|max:5000',
            'email_subject' => 'required|string|max:255',
        ]);

        $message = ContactMessage::findOrFail($id);
        
        // Check if user has access to reply to this message
        if (in_array($user->role, ['receptionist', 'designer'])) {
            $userEmail = $user->email ?? '';
            $userPhone = $user->phone ?? '';
            
            // Normalize phone numbers for comparison
            $normalizePhone = function($phone) {
                if (empty($phone)) return '';
                return preg_replace('/[^\d+]/', '', $phone);
            };
            
            $normalizedUserPhone = $normalizePhone($userPhone);
            $normalizedMessagePhone = $normalizePhone($message->phone);
            
            // Check if email or phone matches
            $hasAccess = false;
            if (!empty($userEmail) && $message->email === $userEmail) {
                $hasAccess = true;
            }
            if (!empty($normalizedUserPhone) && !empty($normalizedMessagePhone) && 
                (strpos($normalizedMessagePhone, $normalizedUserPhone) !== false || 
                 strpos($normalizedUserPhone, $normalizedMessagePhone) !== false)) {
                $hasAccess = true;
            }
            
            if (!$hasAccess) {
                abort(403, 'You do not have permission to reply to this message.');
            }
        }
        
        // Save reply first (so it's not lost if email fails)
        $message->update([
            'admin_reply' => $request->email_reply,
            'status' => 'replied',
            'replied_at' => now(),
            'replied_by' => Auth::id(),
        ]);

        // Dispatch email job to queue (runs in background)
        try {
            $adminName = Auth::user()->name ?? 'CHIBO BRAND Team';
            
            // Dispatch job to send email asynchronously
            SendContactReplyEmail::dispatch(
                $message,
                $request->email_subject,
                $request->email_reply,
                $adminName
            );

            \Log::info('Email job dispatched to queue', [
                'message_id' => $message->id,
                'to' => $message->email
            ]);

            return redirect()->route('admin.contact-messages.show', $message->id)
                ->with('success', 'Reply saved successfully! Email is being sent in the background to ' . $message->email);
                
        } catch (\Exception $e) {
            \Log::error('Failed to dispatch email job', [
                'message_id' => $message->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('admin.contact-messages.show', $message->id)
                ->with('success', 'Reply saved successfully, but email could not be queued: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $message = ContactMessage::findOrFail($id);
        
        // Check if user has access to delete this message
        if (in_array($user->role, ['receptionist', 'designer'])) {
            $userEmail = $user->email ?? '';
            $userPhone = $user->phone ?? '';
            
            // Normalize phone numbers for comparison
            $normalizePhone = function($phone) {
                if (empty($phone)) return '';
                return preg_replace('/[^\d+]/', '', $phone);
            };
            
            $normalizedUserPhone = $normalizePhone($userPhone);
            $normalizedMessagePhone = $normalizePhone($message->phone);
            
            // Check if email or phone matches
            $hasAccess = false;
            if (!empty($userEmail) && $message->email === $userEmail) {
                $hasAccess = true;
            }
            if (!empty($normalizedUserPhone) && !empty($normalizedMessagePhone) && 
                (strpos($normalizedMessagePhone, $normalizedUserPhone) !== false || 
                 strpos($normalizedUserPhone, $normalizedMessagePhone) !== false)) {
                $hasAccess = true;
            }
            
            if (!$hasAccess) {
                abort(403, 'You do not have permission to delete this message.');
            }
        }
        
        $message->delete();

        return redirect()->route('admin.contact-messages.index')
            ->with('success', 'Message deleted successfully!');
    }
}
