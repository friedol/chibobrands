<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Services\AuditLogService;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Handle login authentication with role-based redirection.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        // Trim email to handle any whitespace
        $credentials['email'] = trim($credentials['email']);

        \Log::info('=== UNIFIED LOGIN ATTEMPT ===', [
            'email' => $credentials['email'],
            'email_length' => strlen($credentials['email'])
        ]);

        // Priority 1: Check for Admin/Staff/User login (users table)
        // We prioritize this so staff members who are also customers get their staff access
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();
            
            \Log::info('✅ Admin/User login SUCCESS', [
                'user_id' => $user->id,
                'role' => $user->role
            ]);

            // If user is staff/admin, also log them into the 'admin' guard
            // to satisfy middleware that requires auth:admin
            $adminRoles = ['super_admin', 'admin', 'manager', 'receptionist', 'designer', 'saler', 'operator', 'delivery', 'gatekeeper', 'accountant'];
            if (in_array($user->role, $adminRoles)) {
                Auth::guard('admin')->login($user, $request->boolean('remember'));
                \Log::info('Also logged into admin guard for role: ' . $user->role);
            }
            
            // Log audit for admin/user login
            try {
                AuditLogService::login($user);
            } catch (\Exception $e) {
                \Log::warning('Failed to log audit for login: ' . $e->getMessage());
            }
            
            // Regenerate session for security
            $request->session()->regenerate();

            // Redirect based on user role
            return $this->redirectBasedOnRole($user);
        }

        // Priority 2: Check for Customer login (customers table)
        $customer = \App\Models\Customer::where('email', $credentials['email'])->first();
        
        if ($customer) {
            \Log::info('Customer found in customers table', [
                'customer_id' => $customer->id,
                'verified' => $customer->verified,
                'is_active' => $customer->is_active
            ]);
            
            // Check password first for security
            if (!\Illuminate\Support\Facades\Hash::check($credentials['password'], $customer->password)) {
                \Log::info('❌ Customer login FAILED: Invalid password', [
                    'customer_id' => $customer->id,
                    'email' => $credentials['email']
                ]);
                
                return back()->withErrors([
                    'email' => 'The provided credentials do not match our records.',
                ])->onlyInput('email');
            }
            
            // Now check if customer is verified
            if (!$customer->verified) {
                \Log::info('⚠️ Customer login BLOCKED: Not verified', [
                    'customer_id' => $customer->id,
                    'name' => $customer->name
                ]);
                
                return back()
                    ->with('unverified_customer', [
                        'name' => $customer->name,
                        'email' => $customer->email,
                        'registered_at' => $customer->created_at->format('M d, Y')
                    ])
                    ->onlyInput('email');
            }
            
            // Check if customer is active
            if (!$customer->is_active) {
                \Log::info('❌ Customer login BLOCKED: Inactive account', [
                    'customer_id' => $customer->id
                ]);
                
                return back()->withErrors([
                    'email' => 'Your account has been deactivated. Please contact support at chibobrandsltd@gmail.com or call +255 655 392 319.',
                ])->onlyInput('email');
            }
            
            // All checks passed - proceed with login
            \Log::info('Attempting customer guard login', [
                'customer_id' => $customer->id,
                'email' => $customer->email,
                'remember' => $request->boolean('remember')
            ]);
            
            Auth::guard('customer')->login($customer, $request->boolean('remember'));
            $request->session()->regenerate();
            $customer->update(['last_login_at' => now()]);
            
            // Determine redirect based on customer type and current context
            $redirectRoute = $customer->is_wholesale ? 'b2b.customer.dashboard' : 'retail.customer.dashboard';
            
            \Log::info('✅ Customer login SUCCESS', [
                'customer_id' => $customer->id,
                'guard' => 'customer',
                'auth_check' => Auth::guard('customer')->check(),
                'redirect_to' => route($redirectRoute)
            ]);
            
            return redirect()->intended(route($redirectRoute));
        }

        \Log::info('❌ Login FAILED: Email not found in any table', [
            'email' => $credentials['email']
        ]);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Redirect user based on their role.
     */
    private function redirectBasedOnRole(User $user): RedirectResponse
    {
        switch ($user->role) {
            case 'admin':
            case 'receptionist':
            case 'designer':
            case 'operator':
            case 'super_admin':
            case 'manager':
            case 'accountant':
                if (!$user->verified) {
                    Auth::logout();
                    return redirect()->route('login')->withErrors([
                        'email' => 'Your admin account is not verified yet.',
                    ]);
                }
                // Always redirect to dashboard, ignoring any stored intended URL
                return redirect('/admin/dashboard');

            case 'delivery':
                if (!$user->verified) {
                    Auth::logout();
                    return redirect()->route('login')->withErrors([
                        'email' => 'Your account is not verified yet.',
                    ]);
                }
                return redirect()->route('admin.dashboard'); // Redirect to main dashboard which directs to delivery dashboard

            case 'gatekeeper':
                if (!$user->verified) {
                    Auth::logout();
                    return redirect()->route('login')->withErrors([
                        'email' => 'Your account is not verified yet.',
                    ]);
                }
                return redirect()->route('admin.dashboard'); // Redirect to main dashboard which directs to gatekeeper dashboard

            case 'saler':
                if (!$user->verified) {
                    Auth::logout();
                    return redirect()->route('login')->withErrors([
                        'email' => 'Your admin account is not verified yet.',
                    ]);
                }
                return redirect()->route('admin.saler.my-dashboard');
                
            case 'wholesale_customer':
            case 'retail_customer':
                if (!$user->verified) {
                    Auth::logout();
                    return redirect()->route('login')->withErrors([
                        'email' => 'Your account is not verified yet. Please wait for admin approval. You will receive an email once your account is verified.',
                    ]);
                }
                return redirect()->intended('/customer/dashboard');
                
            default:
                return redirect()->intended('/customer/dashboard');
        }
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();
        
        // Log audit for logout before logging out
        if ($user) {
            try {
                AuditLogService::logout($user);
            } catch (\Exception $e) {
                \Log::warning('Failed to log audit for logout: ' . $e->getMessage());
            }
        }
        
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
