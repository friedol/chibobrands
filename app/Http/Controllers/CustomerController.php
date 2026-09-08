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
use App\Exports\CustomerExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use App\Mail\CustomerVerifiedMail;
use App\Models\Region;
use App\Models\District;

class CustomerController extends Controller
{
    /**
     * Display the customer registration form.
     */
    public function showRegistrationForm(): View
    {
        $regions = \App\Models\Region::orderBy('region_name')->get();
        return view('customer.auth.register', compact('regions'));
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
                'region_id' => 'required|exists:regions,id',
                'district_id' => 'required|exists:districts,id',
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
                'region_id' => $request->region_id,
                'district_id' => $request->district_id,
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
        $regions = \App\Models\Region::orderBy('region_name')->get();
        $districts = $customer->region_id
            ? \App\Models\District::where('region_id', $customer->region_id)->orderBy('district_name')->get()
            : collect([]);
        return view('customer.profile', compact('customer', 'regions', 'districts'));
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
            'region_id' => 'required|exists:regions,id',
            'district_id' => 'required|exists:districts,id',
        ]);

        $customer->update($request->only([
            'name', 'email', 'phone', 'company_name', 'business_type', 'address', 'region_id', 'district_id'
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

        // 1. Only non-salespeople can see all customers
        // Salespeople can see all customers in the list (for reference), but stats only show their own
        if ($user->role === 'saler') {
            // Don't filter query here - let salespeople see all customers
            // But calculate stats based on their customers only
        }

        // Stats calculation - for salespeople show only their customer stats
        $statsBaseQuery = clone $query;
        if ($user->role === 'saler') {
            $statsBaseQuery->forSaler($user);
        }
        
        $newCustomerIds = (clone $statsBaseQuery)->where('is_repeated', false)->pluck('id');
        $repCustomerIds = (clone $statsBaseQuery)->where('is_repeated', true)->pluck('id');
        
        $stats = [
            'total'          => (clone $statsBaseQuery)->count(),
            'new_this_week'  => (clone $statsBaseQuery)->where('created_at', '>=', now()->startOfWeek())->count(),
            'verified'       => (clone $statsBaseQuery)->where('verified', true)->count(),
            'wholesale'      => (clone $statsBaseQuery)->where('is_wholesale', true)->count(),
            'pending'        => (clone $statsBaseQuery)->where('verified', false)->count(),
            'new_customers'  => (clone $statsBaseQuery)->where('is_repeated', false)->count(),
            'repeated_customers' => (clone $statsBaseQuery)->where('is_repeated', true)->count(),
            'new_this_week_new'      => (clone $statsBaseQuery)->where('is_repeated', false)->where('created_at', '>=', now()->startOfWeek())->count(),
            'repeated_this_week'     => (clone $statsBaseQuery)->where('is_repeated', true)->where('updated_at', '>=', now()->startOfWeek())->count(),
            'new_customers_wholesale' => (clone $statsBaseQuery)->where('is_repeated', false)->where('is_wholesale', true)->count(),
            'new_customers_retail'    => (clone $statsBaseQuery)->where('is_repeated', false)->where('is_wholesale', false)->count(),
            'new_customer_revenue'    => \App\Models\Payment::activeFinance()->whereIn('customer_id', $newCustomerIds)->sum('amount'),
            'repeated_customer_revenue' => \App\Models\Payment::activeFinance()->whereIn('customer_id', $repCustomerIds)->sum('amount'),
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

        // 3. Filter by status / customer type
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
                case 'new':
                    $query->where('is_repeated', false);
                    break;
                case 'repeated':
                    $query->where('is_repeated', true);
                    break;
            }
        }

        // 4. Period filter (today / week / month / year)
        [$periodFrom, $periodTo, $periodLabel] = $this->resolvePeriod($request);
        if ($periodFrom && $periodTo) {
            $query->whereBetween('created_at', [$periodFrom, $periodTo]);
        }

        // 5. Salesperson filter — matches however that relationship is actually
        // recorded (added_by, assigned design tasks, or legacy order notes),
        // same logic the "My Customers" toggle uses for the logged-in saler.
        if ($request->filled('saler_id') && $request->saler_id !== 'all') {
            $filterSaler = User::find($request->saler_id);
            $query->broughtBySaler($request->saler_id, $filterSaler?->phone);
        }

        // 6. "My Customers" toggle for salers and senior salers
        if ($request->boolean('own_only') && in_array($user->role, ['saler', 'senior_saler'])) {
            $query->forSaler($user);
        }

        $customers = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $templates = \App\Models\MessageTemplate::active()->get();
        $salers = User::where('role', 'saler')->orderBy('name')->get();
        $duplicateGroups = \App\Services\CustomerJourneyService::findDuplicateCustomers();
        $duplicateCount = $duplicateGroups->count();

        return view('admin.customers.index', compact('customers', 'templates', 'stats', 'salers', 'periodLabel', 'duplicateCount'));
    }

    /**
     * Display customer distribution map on its own page for admin.
     */
    public function adminMap(Request $request): View
    {
        $user = Auth::user();
        $query = Customer::query()->with([
            'region:id,region_name',
            'district:id,district_name,region_id',
            'addedBy:id,name',
        ]);

        // 1. Role-aware filtering for salers
        $query->forSaler($user);

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

        // 3. Filter by status / customer type
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
                case 'new':
                    $query->where('is_repeated', false);
                    break;
                case 'repeated':
                    $query->where('is_repeated', true);
                    break;
            }
        }

        if ($request->filled('region_id') && $request->region_id !== 'all') {
            $query->where('region_id', $request->region_id);
        }

        if ($request->filled('district_id') && $request->district_id !== 'all') {
            $query->where('district_id', $request->district_id);
        }

        // 4. Period filter (today / week / month / year)
        [$periodFrom, $periodTo, $periodLabel] = $this->resolvePeriod($request);
        if ($periodFrom && $periodTo) {
            $query->whereBetween('created_at', [$periodFrom, $periodTo]);
        }

        // 5. Salesperson filter — matches however that relationship is actually
        // recorded (added_by, assigned design tasks, or legacy order notes),
        // same logic the "My Customers" toggle uses for the logged-in saler.
        if ($request->filled('saler_id') && $request->saler_id !== 'all') {
            $filterSaler = User::find($request->saler_id);
            $query->broughtBySaler($request->saler_id, $filterSaler?->phone);
        }

        $geoColumnsAvailable = Schema::hasColumn('customers', 'latitude') && Schema::hasColumn('customers', 'longitude');

        // Calculate Customer Distribution for the Map (respecting filters)
        $regionCountQuery = clone $query;
        $customerCountsByRegion = $regionCountQuery->whereNotNull('region_id')
            ->select('region_id', \DB::raw('count(*) as count'))
            ->groupBy('region_id')
            ->pluck('count', 'region_id')
            ->toArray();

        $regionsWithCoords = \App\Models\Region::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get(['id', 'region_name', 'latitude', 'longitude']);

        $regionDistribution = $regionsWithCoords->map(function($region) use ($customerCountsByRegion) {
            return [
                'name' => $region->region_name,
                'latitude' => (float) $region->latitude,
                'longitude' => (float) $region->longitude,
                'count' => $customerCountsByRegion[$region->id] ?? 0
            ];
        })->filter(function($item) {
            return $item['count'] > 0;
        })->values()->toArray();

        $customerPoints = [];
        if ($geoColumnsAvailable) {
            $customerPoints = (clone $query)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->latest('customers.updated_at')
                ->get([
                    'id',
                    'name',
                    'company_name',
                    'phone',
                    'region_id',
                    'district_id',
                    'latitude',
                    'longitude',
                    'address',
                    'customer_source',
                    'verified',
                    'is_wholesale',
                    'added_by',
                    'created_at',
                ])
                ->map(function ($customer) {
                    return [
                        'id' => $customer->id,
                        'name' => $customer->name,
                        'company_name' => $customer->company_name,
                        'phone' => $customer->phone,
                        'region' => $customer->region?->region_name,
                        'district' => $customer->district?->district_name,
                        'latitude' => (float) $customer->latitude,
                        'longitude' => (float) $customer->longitude,
                        'address' => $customer->address,
                        'customer_source' => $customer->customer_source,
                        'verified' => (bool) $customer->verified,
                        'is_wholesale' => (bool) $customer->is_wholesale,
                        'added_by' => $customer->addedBy?->name,
                        'created_at' => optional($customer->created_at)?->format('d M Y'),
                    ];
                })
                ->values()
                ->toArray();
        }

        // Fallback pins by region center when customer lat/lng is not available.
        if (empty($customerPoints)) {
            $regionPins = collect($regionDistribution)->map(function ($region) {
                return [
                    'id' => 'region-' . $region['name'],
                    'name' => $region['name'],
                    'company_name' => null,
                    'phone' => null,
                    'region' => $region['name'],
                    'district' => null,
                    'latitude' => (float) $region['latitude'],
                    'longitude' => (float) $region['longitude'],
                    'address' => null,
                    'customer_source' => null,
                    'verified' => false,
                    'is_wholesale' => false,
                    'added_by' => null,
                    'created_at' => null,
                    'count' => (int) $region['count'],
                ];
            })->values()->toArray();

            $customerPoints = $regionPins;
        }

        $regions = 
            \App\Models\Region::orderBy('region_name')->get(['id', 'region_name']);

        $districts = $request->filled('region_id') && $request->region_id !== 'all'
            ? \App\Models\District::where('region_id', $request->region_id)->orderBy('district_name')->get(['id', 'district_name', 'region_id'])
            : collect();

        $customersNeedingLocationQuery = (clone $query)
            ->where(function ($q) {
                $q->whereNull('region_id');
            });

        if ($geoColumnsAvailable) {
            $customersNeedingLocationQuery->orWhere(function ($q) {
                $q->whereNull('latitude')->orWhereNull('longitude');
            });

            $customersNeedingLocation = $customersNeedingLocationQuery
                ->latest('customers.updated_at')
                ->take(10)
                ->get(['id', 'name', 'company_name', 'region_id', 'district_id', 'latitude', 'longitude']);
        } else {
            $customersNeedingLocation = $customersNeedingLocationQuery
                ->latest('customers.updated_at')
                ->take(10)
                ->get(['id', 'name', 'company_name', 'region_id', 'district_id']);
        }

        $mapCenter = !empty($customerPoints)
            ? [$customerPoints[0]['latitude'], $customerPoints[0]['longitude']]
            : [-6.3690, 34.8888];

        $salers = User::where('role', 'saler')->orderBy('name')->get();

        return view('admin.customers.map', compact('regionDistribution', 'customerPoints', 'regions', 'districts', 'customersNeedingLocation', 'mapCenter', 'salers', 'periodLabel'));
    }

    public function storeMapRegion(Request $request): RedirectResponse
    {
        if (!Auth::user()->hasPermission('manage_customers') && Auth::user()->role !== 'accountant') {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $validated = $request->validate([
            'region_name' => 'required|string|max:120|unique:regions,region_name',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $baseCode = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $validated['region_name']), 0, 3));
        $baseCode = $baseCode !== '' ? $baseCode : 'REG';
        $regionCode = $baseCode;
        $suffix = 1;

        while (Region::where('region_code', $regionCode)->exists()) {
            $regionCode = $baseCode . str_pad((string) $suffix, 2, '0', STR_PAD_LEFT);
            $suffix++;
        }

        Region::create([
            'region_name' => $validated['region_name'],
            'region_code' => $regionCode,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
        ]);

        return redirect()->route('admin.customers.map')->with('success', 'Region added successfully.');
    }

    public function storeMapDistrict(Request $request): RedirectResponse
    {
        if (!Auth::user()->hasPermission('manage_customers') && Auth::user()->role !== 'accountant') {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $validated = $request->validate([
            'region_id' => 'required|exists:regions,id',
            'district_name' => 'required|string|max:120',
        ]);

        $exists = District::where('region_id', $validated['region_id'])
            ->whereRaw('LOWER(district_name) = ?', [mb_strtolower($validated['district_name'])])
            ->exists();

        if ($exists) {
            return redirect()->route('admin.customers.map')->with('error', 'District already exists for the selected region.');
        }

        District::create([
            'region_id' => $validated['region_id'],
            'district_name' => $validated['district_name'],
        ]);

        return redirect()->route('admin.customers.map')->with('success', 'District added successfully.');
    }

    public function exportExcel(Request $request)
    {
        if (Auth::user()->role === 'saler') {
            abort(403, 'Sellers are not permitted to export customer information.');
        }

        try {
            ini_set('memory_limit', '-1');
            set_time_limit(300);

            $query = $this->buildExportQuery($request)
                ->select([
                    'id',
                    'name',
                    'phone',
                    'email',
                    'is_repeated',
                    'is_wholesale',
                    'verified',
                    'purchase_count',
                    'first_purchase_date',
                    'created_at',
                ]);

            $period = $request->get('period', 'all');
            $filename = 'customers-' . $period . '-' . now()->format('Y-m-d') . '.xlsx';

            return Excel::download(new CustomerExport($query, 'Customers'), $filename);
        } catch (\Throwable $e) {
            Log::error('Customer Excel export failed', [
                'message' => $e->getMessage(),
                'user_id' => Auth::id(),
                'filters' => $request->all(),
            ]);

            return redirect()->back()->with('error', 'Excel export failed. Please try again or reduce filters.');
        }
    }

    public function exportPdf(Request $request)
    {
        if (Auth::user()->role === 'saler') {
            abort(403, 'Sellers are not permitted to print or export customer information.');
        }

        try {
            ini_set('memory_limit', '512M');
            set_time_limit(120);

            $query = $this->buildExportQuery($request)
                ->select([
                    'id',
                    'name',
                    'phone',
                    'email',
                    'is_repeated',
                    'is_wholesale',
                    'verified',
                    'purchase_count',
                    'created_at',
                ]);

            $customers = $query->get();
            [$from, $to, $label] = $this->resolvePeriod($request);

            // Fast browser preview page (users can Print -> Save as PDF).
            return view('admin.customers.export-pdf', compact('customers', 'label', 'from', 'to'));
        } catch (\Throwable $e) {
            Log::error('Customer PDF export failed', [
                'message' => $e->getMessage(),
                'user_id' => Auth::id(),
                'filters' => $request->all(),
            ]);

            return redirect()->back()->with('error', 'PDF export failed. Please try again or reduce filters.');
        }
    }

    private function buildExportQuery(Request $request)
    {
        $user  = Auth::user();
        $query = Customer::query()->forSaler($user);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
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
                case 'new':
                    $query->where('is_repeated', false);
                    break;
                case 'repeated':
                    $query->where('is_repeated', true);
                    break;
            }
        }

        [$from, $to] = $this->resolvePeriod($request);
        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }

        if ($request->filled('saler_id') && $request->saler_id !== 'all') {
            $query->where('added_by', $request->saler_id);
        }

        return $query->orderBy('created_at', 'desc');
    }

    private function resolvePeriod(Request $request): array
    {
        return match ($request->get('period', 'all')) {
            'today'   => [now()->startOfDay(),   now()->endOfDay(),   'Today'],
            'week'    => [now()->startOfWeek(),  now()->endOfWeek(),  'This Week'],
            'month'   => [now()->startOfMonth(), now()->endOfMonth(), 'This Month'],
            'year'    => [now()->startOfYear(),  now()->endOfYear(),  'This Year'],
            default   => [null, null, 'All Time'],
        };
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
        $regions = \App\Models\Region::orderBy('region_name')->get();
        return view('admin.customers.create', compact('salers', 'regions'));
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
            'region_id' => 'nullable|exists:regions,id',
            'district_id' => 'nullable|exists:districts,id',
            'password' => 'nullable|string|min:8|confirmed',
            'added_by' => 'nullable|exists:users,id',
        ]);

        $whatsappNumber = null;
        if ($request->filled('whatsapp_number')) {
            $whatsappNumber = ($request->whatsapp_country_code ?? '+255') . ' ' . ltrim($request->whatsapp_number, '+0-9 ');
        }

        // Determine ownership attributes
        $addedBy = $request->filled('added_by') ? $request->added_by : auth()->id();
        $registeredBy = auth()->id();
        $accountOwner = $request->filled('account_owner_id') ? $request->account_owner_id : $addedBy;
        $branchId = $request->filled('branch_id') ? $request->branch_id : auth()->user()->department_id;
        $source = $request->filled('customer_source') ? $request->customer_source : $request->input('source');

        $customer = Customer::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password ?? 'password'),
            'phone' => $phone,
            'whatsapp_number' => $whatsappNumber,
            'company_name' => $request->company_name,
            'business_type' => $request->business_type,
            'customer_source' => $source,
            'address' => $request->address,
            'region_id' => $request->region_id,
            'district_id' => $request->district_id,
            'is_wholesale' => $request->has('is_wholesale'),
            'verified' => $request->has('verified'),
            'is_active' => $request->has('is_active') || $request->is_active === 'on',
            'added_by' => $addedBy,
            'registered_by_id' => $registeredBy,
            'account_owner_id' => $accountOwner,
            'branch_id' => $branchId,
        ]);

        // If no is_active field is present (e.g. from saler form?), default to true?
        // Admin modal has is_active checked by default. Saler might not see it?
        // Checking modal: all fields are visible, but checkboxes might be missed
        // If saler modal doesn't include is_active, it defaults false.
        // Let's force active if variable missing? No, modal has it.

        // Auto-link any matching leads by phone number
        \App\Services\CustomerJourneyService::linkLeadsToCustomer($customer);

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
        $allOrders = $user
            ? Order::where('user_id', $user->id)->with(['items', 'saler', 'user', 'department', 'customerBusiness'])->latest()->get()
            : collect([]);
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

        // Customer Journey & Timeline Data
        $leads = \App\Models\Lead::where('customer_id', $customer->id)
            ->orWhere(function($q) use ($customer) {
                if ($customer->phone) $q->where('phone', \App\Services\PhoneNormalizationService::normalize($customer->phone));
            })
            ->with(['followUps.user', 'seller'])
            ->latest()
            ->get();

        $customerFollowUps = \App\Models\CustomerFollowUp::where('customer_id', $customer->id)
            ->with('user')
            ->latest()
            ->get();

        $businesses = $customer->businesses()->with(['region', 'district'])->get();
        $orders = $allOrders;
        $debtPayments = \App\Models\Payment::where('customer_id', $customer->id)
            ->where('is_debt', true)
            ->with(['order.department', 'order.customerBusiness', 'designTask.department', 'designTask.customerBusiness', 'customerBusiness', 'seller', 'reconciledByUser'])
            ->latest()
            ->get();
        $smsRecipients = \App\Models\SmsCampaignRecipient::where('customer_id', $customer->id)
            ->orWhere('phone_number', $customer->phone)
            ->with('campaign.sender')
            ->latest()
            ->get();
        $payments = \App\Models\Payment::where('customer_id', $customer->id)
            ->with(['order.department', 'order.customerBusiness', 'designTask.department', 'designTask.customerBusiness', 'customerBusiness', 'seller', 'reconciledByUser'])
            ->latest()
            ->get();
        $regions = \App\Models\Region::orderBy('region_name')->get();

        return view('admin.customers.show', compact(
            'customer', 'orders', 'designTasks', 'lifetimeTotal', 'totalOrders', 'totalTasks',
            'totalOrderValue', 'totalTaskValue', 'periodTotal', 'averageOrderValue',
            'months', 'orderData', 'taskData', 'templates', 'period',
            'leads', 'customerFollowUps', 'businesses', 'smsRecipients', 'payments', 'debtPayments', 'regions'
        ));
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(Customer $customer): View
    {
        $user = Auth::user();
        
        // Allow edit if user has manage_customers permission or is admin/super_admin/manager/accountant/receptionist
        // For salespeople, allow editing only if they added the customer or are the account owner
        if ($user->role === 'saler') {
            if ($customer->added_by !== $user->id && $customer->account_owner_id !== $user->id) {
                abort(403, 'You can only edit customers you added or are assigned to.');
            }
        } elseif (!$user->hasPermission('manage_customers') && !in_array($user->role, ['accountant', 'receptionist', 'admin', 'super_admin', 'manager'])) {
            abort(403, 'You do not have permission to edit customers.');
        }
        $salers = User::where('role', 'saler')->orderBy('name')->get();
        $regions = \App\Models\Region::orderBy('region_name')->get();
        $districts = $customer->region_id
            ? \App\Models\District::where('region_id', $customer->region_id)->orderBy('district_name')->get()
            : collect([]);
        return view('admin.customers.edit', compact('customer', 'salers', 'regions', 'districts'));
    }

    /**
     * Update the specified customer.
     */
    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $user = Auth::user();
        
        // Allow update if user has manage_customers permission or is admin/super_admin/manager/accountant/receptionist
        // For salespeople, allow editing only if they added the customer or are the account owner
        if ($user->role === 'saler') {
            if ($customer->added_by !== $user->id && $customer->account_owner_id !== $user->id) {
                return redirect()->back()->with('error', 'You can only edit customers you added or are assigned to.');
            }
        } elseif (!$user->hasPermission('manage_customers') && !in_array($user->role, ['accountant', 'receptionist', 'admin', 'super_admin', 'manager'])) {
            return redirect()->back()->with('error', 'You do not have permission to edit customers.');
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
            'region_id' => 'nullable|exists:regions,id',
            'district_id' => 'nullable|exists:districts,id',
            'password' => 'nullable|string|min:8|confirmed',
            'added_by' => 'nullable|exists:users,id',
            'account_owner_id' => 'nullable|exists:users,id',
            'branch_id' => 'nullable|exists:departments,id',
            'customer_source' => 'nullable|string|max:255',
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
            'region_id' => $request->region_id,
            'district_id' => $request->district_id,
            'is_wholesale' => $request->has('is_wholesale'),
            'verified' => $request->has('verified'),
            'is_active' => $request->has('is_active'),
        ];

        if ($request->filled('customer_source')) {
            $customerData['customer_source'] = $request->customer_source;
        }

        if ($request->filled('account_owner_id')) {
            $customerData['account_owner_id'] = $request->account_owner_id;
        }

        if ($request->filled('branch_id')) {
            $customerData['branch_id'] = $request->branch_id;
        }

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
     * Transfer customer ownership (Managers and Admins only).
     */
    public function transferOwnershipPage(Customer $customer): RedirectResponse
    {
        return redirect()
            ->route('admin.customers.show', $customer)
            ->with('error', 'Transfer ownership must be submitted from the Transfer form.');
    }

    /**
     * Transfer customer ownership (Managers and Admins only).
     */
    public function transferOwnership(Request $request, Customer $customer): RedirectResponse
    {
        $user = Auth::user();

        if (!in_array($user->role, ['admin', 'super_admin', 'manager']) && !$user->hasPermission('manage_customers')) {
            return redirect()->back()->with('error', 'Unauthorized action. Only Managers and Administrators can transfer customer ownership.');
        }

        $request->validate([
            'account_owner_id' => 'required|exists:users,id',
            'transfer_reason'  => 'nullable|string|max:500',
        ]);

        $oldOwnerName = $customer->accountOwner ? $customer->accountOwner->name : 'Unassigned';
        $newOwnerUser = User::findOrFail($request->account_owner_id);

        $customer->update([
            'account_owner_id' => $newOwnerUser->id,
        ]);

        // Audit log recording
        if (class_exists('\App\Models\AuditLog')) {
            $transferDescription = "Transferred customer #{$customer->id} ({$customer->name}) from '{$oldOwnerName}' to '{$newOwnerUser->name}'. Reason: " . ($request->transfer_reason ?? 'Administrative transfer');

            \App\Models\AuditLog::create([
                'user_id' => $user->id,
                'action'  => 'Transfer Ownership',
                'model_type' => \App\Models\Customer::class,
                'model_id' => $customer->id,
                'description' => $transferDescription,
                'new_values' => [
                    'account_owner_id' => $newOwnerUser->id,
                    'account_owner_name' => $newOwnerUser->name,
                    'transfer_reason' => $request->transfer_reason,
                ],
                'ip_address' => $request->ip(),
            ]);
        }

        return redirect()->back()->with('success', "Customer ownership successfully transferred to {$newOwnerUser->name}!");
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
        $user = Auth::user();
        
        // Allow deletion if user has manage_customers permission or is admin/super_admin/manager/accountant
        // For salespeople, allow deletion only if they added the customer or are the account owner
        if ($user->role === 'saler') {
            if ($customer->added_by !== $user->id && $customer->account_owner_id !== $user->id) {
                return redirect()->back()->with('error', 'You can only delete customers you added or are assigned to.');
            }
        } elseif (!$user->hasPermission('manage_customers') && !in_array($user->role, ['admin', 'super_admin', 'manager', 'accountant'])) {
            return redirect()->back()->with('error', 'You do not have permission to delete customers.');
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

    /**
     * Get districts for a region (AJAX).
     */
    public function getDistrictsForRegion($regionId)
    {
        $districts = \App\Models\District::where('region_id', $regionId)
            ->orderBy('district_name')
            ->get(['id', 'district_name']);
        return response()->json($districts);
    }

    /**
     * List duplicate customers detected by phone format.
     */
    public function duplicates()
    {
        if (!in_array(auth()->user()->role, ['admin', 'super_admin', 'manager'])) {
            abort(403, 'Unauthorized action.');
        }

        $duplicateGroups = \App\Services\CustomerJourneyService::findDuplicateCustomers();
        return view('admin.customers.duplicates', compact('duplicateGroups'));
    }

    /**
     * Merge Customer B into Customer A.
     */
    public function merge(Request $request)
    {
        if (!in_array(auth()->user()->role, ['admin', 'super_admin', 'manager'])) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'target_customer_id' => 'required|exists:customers,id',
            'source_customer_id' => 'required|exists:customers,id|different:target_customer_id',
        ]);

        $target = Customer::findOrFail($request->target_customer_id);
        $source = Customer::findOrFail($request->source_customer_id);

        \App\Services\CustomerJourneyService::mergeCustomers($target, $source, auth()->id());

        return redirect()->route('admin.customers.duplicates')
            ->with('success', "Successfully merged customer record '{$source->name}' into primary customer '{$target->name}'.");
    }

    /**
     * Add a business profile to a customer.
     */
    public function storeBusiness(Request $request, Customer $customer)
    {
        $country = $request->input('country', 'Tanzania');

        $rules = [
            'business_name' => 'required|string|max:255',
            'business_type' => 'nullable|string|max:255',
            'phone'         => 'nullable|string|max:20',
            'email'         => 'nullable|email|max:255',
            'country'       => 'required|string|max:100',
            'address'       => 'nullable|string|max:500',
            'is_primary'    => 'nullable|boolean',
        ];

        if (strtolower(trim($country)) === 'tanzania') {
            $rules['region_id'] = 'required|exists:regions,id';
            $rules['district_id'] = 'required|exists:districts,id';
        } else {
            $rules['region_id'] = 'nullable|exists:regions,id';
            $rules['district_id'] = 'nullable|exists:districts,id';
        }

        $validated = $request->validate($rules);

        $shouldBePrimary = $request->boolean('is_primary') || !$customer->businesses()->exists();
        $validated['is_primary'] = $shouldBePrimary;

        if ($shouldBePrimary) {
            $customer->businesses()->update(['is_primary' => false]);
        }

        $customer->businesses()->create($validated);

        return redirect()->back()->with('success', 'Business profile added successfully.');
    }

    /**
     * Remove a business profile.
     */
    public function destroyBusiness(\App\Models\CustomerBusiness $business)
    {
        $business->delete();
        return redirect()->back()->with('success', 'Business profile removed.');
    }
}
