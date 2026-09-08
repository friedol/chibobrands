<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\UserDevice;
use App\Models\VerificationCode;
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

            // Check for 2FA on new devices
            $twoFaEnabled = $this->get2faSetting();
            $deviceId = $request->cookie('device_id');
            $knownDevice = UserDevice::where('user_id', $user->id)
                ->where('device_identifier', $deviceId)
                ->whereNotNull('verified_at')
                ->exists();

            if ($twoFaEnabled && !$knownDevice) {
                // Logout immediately to prevent access
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                // Generate code
                $code = rand(100000, 999999);
                
                VerificationCode::create([
                    'user_id' => $user->id,
                    'code' => $code,
                    'expires_at' => now()->addMinutes(10),
                ]);

                // Send code (Email)
                \Log::info("2FA Code for User {$user->id}: {$code}");
                
                try {
                    \Illuminate\Support\Facades\Mail::raw("Your CHIBO BRANDS Verification Code is: {$code}. Don't share it with anyone.", function ($message) use ($user) {
                        $message->to($user->email)->subject('Your CHIBO BRANDS Verification Code');
                    });
                } catch (\Exception $e) {
                    \Log::error("Failed to send 2FA email: " . $e->getMessage());
                }

                // Send code (SMS via Beem Africa)
                if ($user->phone) {
                    try {
                        $smsService = app(\App\Services\SmsApiService::class);
                        $result = $smsService->sendSMS($user->phone, "Your CHIBO BRANDS Verification Code is: {$code}. Don't share it with anyone.");
                        
                        if (isset($result['success']) && $result['success']) {
                            \Log::info("2FA SMS sent to {$user->phone} via Beem Africa");
                        } else {
                            \Log::warning("Failed to send 2FA SMS: " . ($result['message'] ?? 'Unknown error'));
                        }
                    } catch (\Exception $e) {
                        \Log::error("Failed to send 2FA SMS: " . $e->getMessage());
                    }
                }

                // Store user ID in session for verification step
                session(['2fa_user_id' => $user->id, '2fa_required' => true]);

                return redirect()->route('2fa.form');
            }

            // If user is staff/admin, also log them into the 'admin' guard
            // to satisfy middleware that requires auth:admin
            $adminRoles = ['super_admin', 'admin', 'manager', 'receptionist', 'designer', 'saler', 'operator', 'delivery', 'gatekeeper', 'accountant', 'marketing_manager', 'hr_officer'];
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

    private function get2faSetting(): bool
    {
        $path = storage_path('app/system_settings.json');
        if (!file_exists($path)) return true;
        $data = json_decode(file_get_contents($path), true);
        return $data['two_fa_enabled'] ?? true;
    }

    /**
     * Show the 2FA verification form.
     */
    public function show2faForm(): View
    {
        return view('auth.2fa');
    }

    /**
     * Verify the 2FA code.
     */
    public function verify2fa(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $userId = session('2fa_user_id');
        
        if (!$userId) {
            return redirect()->route('login');
        }

        $verification = VerificationCode::where('user_id', $userId)
            ->where('code', $request->code)
            ->where('expires_at', '>', now())
            ->first();

        if (!$verification) {
            return back()->with('error', 'Invalid or expired verification code.');
        }

        // Delete code
        $verification->delete();

        // Mark device as verified
        $user = User::find($userId);
        
        // Generate a device token
        $deviceToken = \Illuminate\Support\Str::random(60);
        
        UserDevice::create([
            'user_id' => $user->id,
            'device_identifier' => $deviceToken,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'verified_at' => now(),
        ]);

        // Set cookie (valid for 30 days)
        cookie()->queue('device_id', $deviceToken, 60 * 24 * 30);

        // Log the user in
        Auth::login($user);
        
        $request->session()->regenerate();
        
        // Clear session
        session()->forget(['2fa_user_id', '2fa_required']);

        return $this->redirectBasedOnRole($user);
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
            case 'marketing_manager':
            case 'hr_officer':
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
