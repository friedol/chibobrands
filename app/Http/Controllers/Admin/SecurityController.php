<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Services\AuditLogService;

class SecurityController extends Controller
{
    /**
     * Show the password reset form.
     */
    public function showResetPasswordForm(): View
    {
        // Only super admin can access this
        if (Auth::user()->role !== 'super_admin') {
            abort(403, 'Only super admin can reset passwords.');
        }

        // Get all users for the dropdown (excluding customers)
        $users = User::whereIn('role', ['super_admin', 'admin', 'manager', 'saler', 'receptionist', 'designer', 'operator', 'accountant', 'marketing_manager', 'hr_officer', 'delivery', 'gatekeeper'])
            ->orderBy('name')
            ->get();

        return view('admin.security.reset-password', compact('users'));
    }

    /**
     * Reset user password.
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        // Only super admin can reset passwords
        if (Auth::user()->role !== 'super_admin') {
            abort(403, 'Only super admin can reset passwords.');
        }

        $validated = $request->validate([
            'user_type' => 'required|in:user,customer',
            'user_id' => 'required|integer',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $currentUser = Auth::user();
        
        if ($validated['user_type'] === 'user') {
            $user = User::findOrFail($validated['user_id']);
            $oldPasswordHash = $user->password; // Store for audit (not the actual password)
            
            $user->update([
                'password' => Hash::make($validated['password']),
            ]);

            // Log audit
            try {
                AuditLogService::log('updated', 'Password reset for user: ' . $user->name . ' (' . $user->email . ') by superadmin', $user);
            } catch (\Exception $e) {
                \Log::warning('Failed to log audit for password reset: ' . $e->getMessage());
            }

            return redirect()->route('admin.security.reset-password')
                ->with('success', 'Password has been reset successfully for user: ' . $user->name);
        } else {
            $customer = Customer::findOrFail($validated['user_id']);
            $customer->update([
                'password' => Hash::make($validated['password']),
            ]);

            // Log audit
            try {
                AuditLogService::log('updated', 'Password reset for customer: ' . $customer->name . ' (' . $customer->email . ') by superadmin', null, null, ['customer_id' => $customer->id]);
            } catch (\Exception $e) {
                \Log::warning('Failed to log audit for password reset: ' . $e->getMessage());
            }

            return redirect()->route('admin.security.reset-password')
                ->with('success', 'Password has been reset successfully for customer: ' . $customer->name);
        }
    }

    /**
     * Search for customers by email, name, or phone (AJAX).
     */
    public function searchUsers(Request $request)
    {
        // Only super admin can search
        if (Auth::user()->role !== 'super_admin') {
            return response()->json(['customers' => []]);
        }

        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json(['customers' => []]);
        }

        $customers = Customer::where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%")
                  ->orWhere('phone', 'like', "%{$query}%");
            })
            ->limit(20)
            ->get()
            ->map(function($customer) {
                return [
                    'id' => $customer->id,
                    'type' => 'customer',
                    'name' => $customer->name,
                    'email' => $customer->email ?? '',
                    'phone' => $customer->phone ?? '',
                ];
            });

        return response()->json([
            'customers' => $customers,
        ]);
    }
}
