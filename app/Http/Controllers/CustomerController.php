<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\Customer;
use App\Models\User;
use App\Models\Order;
use App\Models\DesignTask;
use App\Models\MessageTemplate;
use App\Notifications\NewRegistrationPending;
use App\Notifications\AccountVerified;
use App\Notifications\RegistrationPendingCustomer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\CustomerVerifiedMail;

class CustomerController extends Controller
{
    /**
     * Display the customer registration form.
     */
    public function showRegistrationForm(): View
    {
        return view('customer.auth.register');
    }

    /**
     * Handle customer registration.
     */
    public function register(Request $request): RedirectResponse
    {
        Log::info('=== REGISTRATION ATTEMPT START ===', [
            'request_data' => $request->except(['password', 'password_confirmation']),
            'has_password' => !empty($request->password),
            'has_password_confirmation' => !empty($request->password_confirmation)
        ]);

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'nullable|string|email|max:255|unique:customers,email',
                'password' => 'required|string|min:8|confirmed',
                'phone' => 'required|string|max:20|unique:customers,phone',
                'company_name' => 'nullable|string|max:255',
                'business_type' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:500',
                'is_wholesale' => 'nullable|boolean',
                'terms' => 'required|accepted',
            ]);
            
            Log::info('✅ Validation passed successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('❌ Validation failed', [
                'errors' => $e->errors()
            ]);
            throw $e;
        }

        // Create customer in customers table
        try {
            $customer = Customer::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'company_name' => $request->company_name,
                'business_type' => $request->business_type,
                'address' => $request->address,
                'is_wholesale' => $request->has('is_wholesale') ? true : false,
                'verified' => false, // New customers start as unverified
                'is_active' => true,
            ]);
            
            Log::info('✅ Customer created successfully', [
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'customer_email' => $customer->email
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Failed to create customer', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }

        // DO NOT auto-login - customer must wait for verification

        Log::info('=== NEW CUSTOMER REGISTRATION ===', [
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_phone' => $customer->phone
        ]);

        // Send notification to customer about pending verification
        try {
            $customer->notify(new RegistrationPendingCustomer());
            Log::info('Registration pending email sent to customer', [
                'customer_id' => $customer->id,
                'email' => $customer->email
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send registration pending notification', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        // Send notification to all admins about new registration
        $admins = User::where('role', 'admin')->get();
        Log::info('Sending new registration notifications to admins', [
            'admin_count' => $admins->count()
        ]);
        
        foreach ($admins as $admin) {
            try {
                $admin->notify(new NewRegistrationPending($customer));
                Log::info('Admin notification sent', [
                    'admin_id' => $admin->id,
                    'admin_email' => $admin->email
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send admin notification', [
                    'admin_id' => $admin->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return redirect()->route('login')
            ->with('success', 'Registration successful! Your account is pending verification. You will receive an email confirmation shortly. Please check your email inbox (and spam folder).');
    }

    /**
     * Display the customer login form.
     */
    public function showLoginForm(): View
    {
        return view('customer.auth.login');
    }

    /**
     * Handle customer login.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        Log::info('=== LOGIN ATTEMPT START ===', [
            'email' => $credentials['email'],
            'has_password' => !empty($credentials['password'])
        ]);

        // Find customer by email
        $customer = Customer::where('email', $credentials['email'])->first();

        Log::info('Customer lookup result', [
            'customer_found' => $customer ? 'YES' : 'NO',
            'customer_id' => $customer ? $customer->id : null,
            'customer_verified' => $customer ? $customer->verified : null,
            'customer_active' => $customer ? $customer->is_active : null
        ]);

        // If customer doesn't exist, show generic error
        if (!$customer) {
            Log::info('❌ Login FAILED: Email not found in database', [
                'email' => $credentials['email']
            ]);
            
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        }

        // Check password first for security
        if (!Hash::check($credentials['password'], $customer->password)) {
            Log::info('❌ Login FAILED: Invalid password', [
                'customer_id' => $customer->id,
                'email' => $credentials['email']
            ]);
            
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        }

        // Now check if customer is verified - show modal
        if (!$customer->verified) {
            Log::info('⚠️ Login BLOCKED: Customer not verified', [
                'customer_id' => $customer->id,
                'email' => $customer->email,
                'name' => $customer->name,
                'verified' => $customer->verified,
                'returning_modal_data' => true
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
            Log::info('Login attempt by inactive customer', [
                'customer_id' => $customer->id,
                'email' => $customer->email
            ]);
            
            return back()->withErrors([
                'email' => 'Your account has been deactivated. Please contact support at chibobrandsltd@gmail.com or call +255 655 392 319.',
            ])->onlyInput('email');
        }

        // All checks passed - proceed with login
        Auth::guard('customer')->login($customer, $request->boolean('remember'));
        $request->session()->regenerate();
        
        // Update last login
        $customer->update(['last_login_at' => now()]);

        Log::info('✅ Login SUCCESSFUL', [
            'customer_id' => $customer->id,
            'email' => $customer->email,
            'name' => $customer->name
        ]);

        return redirect()->intended(route('customer.dashboard'));
    }

    /**
     * Handle customer logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Display the customer dashboard.
     */
    public function dashboard(): View
    {
        $customer = Auth::guard('customer')->user();
        
        // Find or create corresponding user record for the customer
        $user = \App\Models\User::firstOrCreate(
            ['email' => $customer->email ?: $customer->phone . '@chibobrand.com'],
            [
                'name' => $customer->name,
                'email' => $customer->email ?: $customer->phone . '@chibobrand.com',
                'phone' => $customer->phone,
                'password' => bcrypt('password'), // Default password
                'role' => $customer->is_wholesale ? 'wholesale_customer' : 'retail_customer',
                'verified' => $customer->verified,
                'is_active' => $customer->is_active,
            ]
        );
        
        // Get recent orders for the customer
        $recentOrders = \App\Models\Order::with(['items.product'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        Log::info('Customer dashboard accessed', [
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'user_id' => $user->id,
            'orders_count' => $recentOrders->count(),
            'orders' => $recentOrders->pluck('order_code')->toArray()
        ]);

        return view('customer.dashboard', compact('customer', 'recentOrders'));
    }

    /**
     * Display products for customer (retail or wholesale based on customer type).
     */
    public function products(Request $request): View
    {
        $customer = Auth::guard('customer')->user();
        
        if ($customer->is_wholesale) {
            return app(ProductDisplayController::class)->indexWholesale($request);
        } else {
            return app(ProductDisplayController::class)->indexRetail($request);
        }
    }

    /**
     * Search products for customer (retail or wholesale based on customer type).
     */
    public function searchProducts(Request $request): View
    {
        $customer = Auth::guard('customer')->user();
        
        if ($customer->is_wholesale) {
            return app(ProductController::class)->wholesaleSearch($request);
        } else {
            return app(ProductController::class)->search($request);
        }
    }

    /**
     * Show product details for customer (retail or wholesale based on customer type).
     */
    public function showProduct($barcode): View
    {
        $customer = Auth::guard('customer')->user();
        
        // Use ProductDisplayController which handles both retail and wholesale
        return app(ProductDisplayController::class)->show($barcode);
    }

    /**
     * Display categories for customer (retail or wholesale based on customer type).
     */
    public function categories(Request $request): View
    {
        $customer = Auth::guard('customer')->user();
        
        if ($customer->is_wholesale) {
            return app(CategoryController::class)->wholesaleIndex($request);
        } else {
            return app(CategoryController::class)->index($request);
        }
    }

    /**
     * Show category details for customer (retail or wholesale based on customer type).
     */
    public function showCategory($category): View
    {
        $customer = Auth::guard('customer')->user();
        
        if ($customer->is_wholesale) {
            return app(CategoryController::class)->wholesaleShow($category);
        } else {
            return app(CategoryController::class)->show($category);
        }
    }

    /**
     * Display the customer profile.
     */
    public function profile(): View
    {
        $customer = Auth::guard('customer')->user();
        return view('customer.profile', compact('customer'));
    }

    /**
     * Update the customer profile.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $customer = Auth::guard('customer')->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:customers,email,' . $customer->id,
            'phone' => 'required|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'business_type' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
        ]);

        $customer->update($request->only([
            'name', 'email', 'phone', 'company_name', 'business_type', 'address'
        ]));

        return redirect()->route('customer.profile')
            ->with('success', 'Profile updated successfully.');
    }

    /**
     * Display the change password form.
     */
    public function showChangePasswordForm(): View
    {
        return view('customer.change-password');
    }

    /**
     * Handle password change.
     */
    public function changePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $customer = Auth::guard('customer')->user();

        if (!Hash::check($request->current_password, $customer->password)) {
            return back()->withErrors([
                'current_password' => 'The current password is incorrect.',
            ]);
        }

        $customer->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('customer.profile')
            ->with('success', 'Password changed successfully.');
    }

    /**
     * Update customer status (admin only).
     */
    public function updateStatus(Request $request, Customer $customer): RedirectResponse
    {
        $request->validate([
            'verified' => 'required|boolean',
        ]);

        $customer->update([
            'verified' => $request->verified,
        ]);

        return redirect()->route('admin.customers.show', $customer)
            ->with('success', 'Customer status updated successfully.');
    }

    /**
     * Display a listing of customers for admin.
     */
    public function adminIndex(Request $request): View
    {
        $user = Auth::user();
        $query = Customer::query();

        // 1. Role-aware filtering for salers
        $query->forSaler($user);

        // Stats calculation (respecting the base filter)
        $statsBaseQuery = clone $query;
        $stats = [
            'total' => (clone $statsBaseQuery)->count(),
            'new_this_week' => (clone $statsBaseQuery)->where('created_at', '>=', now()->subDays(7))->count(),
            'verified' => (clone $statsBaseQuery)->where('verified', true)->count(),
            'wholesale' => (clone $statsBaseQuery)->where('is_wholesale', true)->count(),
            'pending' => (clone $statsBaseQuery)->where('verified', false)->count(),
        ];
        $stats['verified_percent'] = $stats['total'] > 0 ? round(($stats['verified'] / $stats['total']) * 100) : 0;

        // 2. Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        // 3. Filter by status
        if ($request->filled('status')) {
            switch ($request->status) {
                case 'verified':
                    $query->where('verified', true);
                    break;
                case 'unverified':
                    $query->where('verified', false);
                    break;
                case 'wholesale':
                    $query->where('is_wholesale', true);
                    break;
                case 'active':
                    $query->where('verified', true);
                    break;
            }
        }

        $customers = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $templates = \App\Models\MessageTemplate::active()->get();
        $salers = User::where('role', 'saler')->orderBy('name')->get();

        return view('admin.customers.index', compact('customers', 'templates', 'stats', 'salers'));
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create(): View
    {
        if (!Auth::user()->hasPermission('manage_customers') && Auth::user()->role !== 'accountant') {
            abort(403, 'Unauthorized action.');
        }
        $salers = User::where('role', 'saler')->orderBy('name')->get();
        return view('admin.customers.create', compact('salers'));
    }

    /**
     * Store a newly created customer.
     */
    public function store(Request $request): RedirectResponse
    {
        if (!Auth::user()->hasPermission('manage_customers') && Auth::user()->role !== 'accountant') {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        // Pre-calculate formatted phone for validation
        $phone = ($request->phone_country_code ?? '+255') . ' ' . ltrim($request->phone, '+0-9 ');
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:customers',
            'phone' => ['required', 'string', 'max:20', function ($attribute, $value, $fail) use ($phone) {
                if (Customer::where('phone', $phone)->exists()) {
                    $fail('The phone number ' . $phone . ' has already been taken.');
                }
            }],
            'company_name' => 'nullable|string|max:255',
            'business_type' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'password' => 'nullable|string|min:8|confirmed',
            'added_by' => 'nullable|exists:users,id',
        ]);

        $whatsappNumber = null;
        if ($request->filled('whatsapp_number')) {
            $whatsappNumber = ($request->whatsapp_country_code ?? '+255') . ' ' . ltrim($request->whatsapp_number, '+0-9 ');
        }

        // Determine added_by: if provided (admin), use it; otherwise use auth user (saler)
        $addedBy = $request->filled('added_by') ? $request->added_by : auth()->id();

        $customer = Customer::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password ?? 'password'),
            'phone' => $phone,
            'whatsapp_number' => $whatsappNumber,
            'company_name' => $request->company_name,
            'business_type' => $request->business_type,
            'address' => $request->address,
            'is_wholesale' => $request->has('is_wholesale'),
            'verified' => $request->has('verified'), // Salers form might default this to false, check modal
            'is_active' => $request->has('is_active') || $request->is_active === 'on', // Handle 'on' or boolean
            'added_by' => $addedBy,
        ]);

        // If no is_active field is present (e.g. from saler form?), default to true?
        // Admin modal has is_active checked by default. Saler might not see it?
        // Checking modal: all fields are visible, but checkboxes might be missed
        // If saler modal doesn't include is_active, it defaults false.
        // Let's force active if variable missing? No, modal has it.

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer ' . $customer->name . ' created successfully!');
    }

    /**
     * Display the specified customer for admin.
     */
    public function adminShow(Customer $customer, Request $request): View
    {
        $period = $request->get('period', '6_months');
        $dateRange = match($period) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            '6_months' => [now()->subMonths(6), now()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            '2_years' => [now()->subYears(2), now()],
            'custom' => [
                $request->get('start_date') ? Carbon::parse($request->get('start_date'))->startOfDay() : null,
                $request->get('end_date') ? Carbon::parse($request->get('end_date'))->endOfDay() : null
            ],
            'all' => [null, null],
            default => [now()->subMonths(6), now()]
        };

        // Resolve User to get Orders
        $user = User::where('email', $customer->email)
                    ->when($customer->phone, function($q) use ($customer) {
                        $q->orWhere('phone', $customer->phone);
                    })->first();

        // Fetch Base Data (All Time)
        $allOrders = $user ? Order::where('user_id', $user->id)->with('items')->latest()->get() : collect([]);
        $allTasks = DesignTask::where('customer_id', $customer->id)->latest()->get();

        // Calculate Lifetime Grand Total (using the stored analytics for consistency)
        $lifetimeTotal = $customer->total_spent;

        // Filter Data for Current View
        $startDate = $dateRange[0];
        $endDate = $dateRange[1];

        $orders = $allOrders;
        $designTasks = $allTasks;

        if ($startDate) {
            $orders = $orders->filter(fn($item) => $item->created_at >= $startDate);
            $designTasks = $designTasks->filter(fn($item) => $item->created_at >= $startDate);
        }
        if ($endDate) {
            $orders = $orders->filter(fn($item) => $item->created_at <= $endDate);
            $designTasks = $designTasks->filter(fn($item) => $item->created_at <= $endDate);
        }

        // Stats for Selected Period
        $totalOrders = $orders->count();
        $totalTasks = $designTasks->count();
        $totalOrderValue = $orders->sum('total_amount');
        $totalTaskValue = $designTasks->sum(fn($t) => $t->requires_receipt ? $t->price * 1.18 : $t->price);
        $periodTotal = $totalTaskValue; // Focus period total on design tasks
        $averageOrderValue = $totalTasks > 0 ? $totalTaskValue / $totalTasks : 0;

        // Chart Data Generation
        $months = collect([]);
        $orderData = collect([]);
        $taskData = collect([]);

        if (in_array($period, ['week', 'month', 'today', 'yesterday', 'custom']) && $startDate && $endDate && $startDate->diffInDays($endDate) <= 31) {
            // Daily Granularity
            $loopStart = $startDate->copy();
            $loopEnd = $endDate->copy();
            
            if ($period === 'today' || ($startDate && $endDate && $startDate->isSameDay($endDate))) {
                  for($h=0; $h<=20; $h+=4) { 
                      $hStart = $startDate->copy()->startOfDay()->addHours($h);
                      $hEnd = $hStart->copy()->addHours(3)->endOfHour();
                      $label = $hStart->format('H:00') . '-' . $hEnd->format('H:00');
                      $months->push($label);
                      $orderData->push($orders->filter(fn($o) => $o->created_at >= $hStart && $o->created_at <= $hEnd)->sum('total_amount'));
                      $taskData->push($designTasks->filter(fn($t) => $t->created_at >= $hStart && $t->created_at <= $hEnd)->sum('price'));
                  }
            } else {
                while($loopStart <= $loopEnd) {
                    $key = $loopStart->format('Y-m-d');
                    $months->push($loopStart->format('M d'));
                    $orderData->push($orders->filter(fn($o) => $o->created_at->format('Y-m-d') === $key)->sum('total_amount'));
                    $taskData->push($designTasks->filter(fn($t) => $t->created_at->format('Y-m-d') === $key)->sum('price'));
                    $loopStart->addDay();
                }
            }
        } else {
            // Monthly Granularity
            $loopStart = ($startDate ?? now()->subYears(2))->copy()->startOfMonth();
            $loopEnd = ($endDate ?? now())->copy()->startOfMonth();
            
            while($loopStart <= $loopEnd) {
                $key = $loopStart->format('Y-m');
                $months->push($loopStart->format('M Y'));
                $orderData->push($orders->filter(fn($o) => $o->created_at->format('Y-m') === $key)->sum('total_amount'));
                $taskData->push($designTasks->filter(fn($t) => $t->created_at->format('Y-m') === $key)->sum('price'));
                $loopStart->addMonth();
            }
        }

        $templates = MessageTemplate::active()->get();
        return view('admin.customers.show', compact(
            'customer', 'templates', 'period', 'orders', 'designTasks', 
            'totalOrders', 'totalTasks', 'totalOrderValue', 'totalTaskValue',
            'periodTotal', 'lifetimeTotal', 'averageOrderValue',
            'months', 'orderData', 'taskData'
        ));
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(Customer $customer): View
    {
        if (!Auth::user()->hasPermission('manage_customers') && !in_array(Auth::user()->role, ['accountant', 'receptionist'])) {
            abort(403, 'Unauthorized action.');
        }
        $salers = User::where('role', 'saler')->orderBy('name')->get();
        return view('admin.customers.edit', compact('customer', 'salers'));
    }

    /**
     * Update the specified customer.
     */
    public function update(Request $request, Customer $customer): RedirectResponse
    {
        if (!Auth::user()->hasPermission('manage_customers') && !in_array(Auth::user()->role, ['accountant', 'receptionist'])) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        // Pre-calculate formatted phone
        $phone = ($request->phone_country_code ?? '+255') . ' ' . ltrim($request->phone, '+0-9 ');
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:customers,email,' . $customer->id,
            'phone' => ['required', 'string', 'max:20', function ($attribute, $value, $fail) use ($phone, $customer) {
                if (Customer::where('phone', $phone)->where('id', '!=', $customer->id)->exists()) {
                    $fail('The phone number ' . $phone . ' has already been taken.');
                }
            }],
            'company_name' => 'nullable|string|max:255',
            'business_type' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'password' => 'nullable|string|min:8|confirmed',
            'added_by' => 'nullable|exists:users,id',
        ]);

    $whatsappNumber = null;
    if ($request->filled('whatsapp_number')) {
        $whatsappNumber = ($request->whatsapp_country_code ?? '+255') . ' ' . ltrim($request->whatsapp_number, '+0-9 ');
    }

    $customerData = [
        'name' => $request->name,
        'email' => $request->email,
        'phone' => $phone,
        'whatsapp_number' => $whatsappNumber,
        'company_name' => $request->company_name,
        'business_type' => $request->business_type,
        'address' => $request->address,
        'is_wholesale' => $request->has('is_wholesale'),
        'verified' => $request->has('verified'),
        'is_active' => $request->has('is_active'),
    ];

        if ($request->has('added_by')) {
            $customerData['added_by'] = $request->added_by;
        }

        if ($request->filled('password')) {
            $customerData['password'] = Hash::make($request->password);
        }

        $customer->update($customerData);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer updated successfully!');
    }

    /**
     * Verify a customer account.
     */
    public function verify($customerId): RedirectResponse
    {
        if (!Auth::user()->hasPermission('manage_customers') && Auth::user()->role !== 'accountant') {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }
        try {
            // Manually find the customer since route model binding is bypassed
            $customer = Customer::findOrFail($customerId);
            
            Log::info('=== VERIFY METHOD STARTED ===', [
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'customer_email' => $customer->email,
                'current_verified' => $customer->verified,
                'exists' => $customer->exists,
                'was_recently_created' => $customer->wasRecentlyCreated,
                'request_method' => request()->method(),
                'request_url' => request()->fullUrl()
            ]);
            
            // Update verification status using update() method
            $saved = $customer->update([
                'verified' => true,
                'is_active' => true
            ]);
            
            // Refresh from database to confirm
            $customer->refresh();
            
            Log::info('=== VERIFY METHOD - SAVE RESULT ===', [
                'save_successful' => $saved,
                'customer_id' => $customer->id,
                'new_verified_status' => $customer->verified,
                'is_active_status' => $customer->is_active,
                'can_login' => ($customer->verified && $customer->is_active)
            ]);
            
            // Send verification email to customer
            try {
                Mail::to($customer->email)->send(new CustomerVerifiedMail($customer));
                Log::info('Verification email sent successfully', [
                    'customer_id' => $customer->id,
                    'email' => $customer->email
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send verification email', [
                    'customer_id' => $customer->id,
                    'error' => $e->getMessage()
                ]);
            }
            
            // Send notification to customer
            try {
                $customer->notify(new AccountVerified());
                Log::info('Account verified notification sent', [
                    'customer_id' => $customer->id
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send account verified notification', [
                    'customer_id' => $customer->id,
                    'error' => $e->getMessage()
                ]);
            }
            
            Log::info('=== VERIFY METHOD COMPLETED SUCCESSFULLY ===');
            
            return redirect()->route('admin.customers.index')
                ->with('success', 'Customer account verified successfully! Verification email sent to ' . $customer->email);
                
        } catch (\Exception $e) {
            Log::error('=== VERIFY METHOD FAILED ===', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.customers.index')
                ->with('error', 'Failed to verify customer: ' . $e->getMessage());
        }
    }

    /**
     * Unverify a customer account (for testing).
     */
    public function unverify(Customer $customer): RedirectResponse
    {
        Log::info('Unverifying customer for testing', ['customer_id' => $customer->id]);
        
        $customer->verified = false;
        $customer->save();
        
        return redirect()->route('admin.customers.show', $customer)
            ->with('success', 'Customer unverified (for testing purposes)');
    }

    /**
     * Remove the specified customer.
     */
    public function destroy(Customer $customer): RedirectResponse
    {
        if (!Auth::user()->hasPermission('manage_customers') && Auth::user()->role !== 'accountant') {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }
        // Check if customer has orders via valid User relationship
        // Since orders are attached to Users, we need to check if there's a User
        // with the same email or phone that has orders
        $hasOrders = false;

        // Check by email
        if (!empty($customer->email)) {
            $user = User::where('email', $customer->email)->first();
            if ($user && $user->orders()->exists()) {
                $hasOrders = true;
            }
        }

        // Check by phone if not found yet
        if (!$hasOrders && !empty($customer->phone)) {
            $user = User::where('phone', $customer->phone)->first();
            if ($user && $user->orders()->exists()) {
                $hasOrders = true;
            }
        }

        if ($hasOrders) {
            return redirect()->back()
                ->with('error', 'Cannot delete customer with existing orders.');
        }

        $customer->delete();

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer deleted successfully!');
    }

}
