<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TestSmsController;
use App\Http\Controllers\EnhancedProductController;
use App\Http\Controllers\ProductDisplayController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\DesignTaskController;
use App\Http\Controllers\SalerPerformanceController;
use App\Http\Controllers\MessageTemplateController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// ============================================================================
// PUBLIC ROUTES (Retail Website - https://chibobrand.com)
// ============================================================================

// Retail Landing Page
Route::get('/', [HomeController::class, 'index'])->name('home');

// Our Brand page (Retail)
Route::get('/our-brand', [HomeController::class, 'ourBrand'])->name('our-brand');

// Unified login route
Route::get('/login', [App\Http\Controllers\AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [App\Http\Controllers\AuthController::class, 'login']);
Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');

// Region-District AJAX lookup
Route::get('/regions/{regionId}/districts', [CustomerController::class, 'getDistrictsForRegion'])->name('regions.districts');

// 2Factor Authentication Routes
Route::get('/2fa', [App\Http\Controllers\AuthController::class, 'show2faForm'])->name('2fa.form');
Route::post('/2fa', [App\Http\Controllers\AuthController::class, 'verify2fa'])->name('2fa.verify');

// Password Reset Routes
Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->middleware('guest')->name('password.request');

Route::post('/forgot-password', [App\Http\Controllers\Auth\PasswordResetLinkController::class, 'store'])
    ->middleware('guest')
    ->name('password.email');

Route::get('/reset-password/{token}', [App\Http\Controllers\Auth\NewPasswordController::class, 'create'])
    ->middleware('guest')
    ->name('password.reset');

Route::post('/reset-password', [App\Http\Controllers\Auth\NewPasswordController::class, 'store'])
    ->middleware('guest')
    ->name('password.update');

Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/services', [HomeController::class, 'services'])->name('services');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');
Route::get('/privacy', [HomeController::class, 'privacy'])->name('privacy');
Route::get('/terms', [HomeController::class, 'terms'])->name('terms');

// Shareable finance daily report (signed URL, no login required — for sharing to external/public app)
Route::get('/share/finance/daily-report', [\App\Http\Controllers\Admin\FinanceController::class, 'dailyReportShared'])
    ->name('finance.daily-report.shared')
    ->middleware('signed');

// Public endpoints for hero slide ad tracking (no auth)
Route::get('/hero-slides/{heroSlide}/track-click', [\App\Http\Controllers\Admin\HeroSlideController::class, 'trackClick'])
    ->name('public.hero-slides.track-click')
    ->withoutMiddleware(['auth', 'admin']);
Route::get('/hero-slides/{heroSlide}/track-impression', [\App\Http\Controllers\Admin\HeroSlideController::class, 'trackImpression'])
    ->name('public.hero-slides.track-impression')
    ->withoutMiddleware(['auth', 'admin']);

// Cart routes
Route::get('/cart', [CartController::class, 'index'])->name('cart');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::put('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::delete('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
Route::get('/cart/get', [CartController::class, 'get'])->name('cart.get');
Route::post('/cart/sync', [CartController::class, 'sync'])->name('cart.sync');
Route::get('/checkout', [CartController::class, 'checkout'])->name('checkout');
Route::post('/checkout', [CartController::class, 'processCheckout'])->name('checkout.process');

// Product routes for retail
Route::prefix('products')->name('products.')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('index');
    Route::get('/search', [ProductController::class, 'search'])->name('search');
    Route::get('/autocomplete', [ProductDisplayController::class, 'autocompleteRetail'])->name('autocomplete');
    Route::get('/get-price/{id}', [ProductController::class, 'getPrice'])->name('get-price');
    Route::get('/{product}', [ProductController::class, 'show'])->name('show');
});

// Public product listing (retail/wholesale) - channel-specific landings
Route::get('/shop', [ProductDisplayController::class, 'indexRetail'])->name('retail.products');
Route::get('/b2b/shop', [ProductDisplayController::class, 'indexWholesale'])->name('wholesale.products');
// Product details - Retail
Route::get('/product/{barcode}', [ProductDisplayController::class, 'show'])->name('product.show');

// Product details - Wholesale
Route::get('/b2b/product/{barcode}', [ProductDisplayController::class, 'show'])->name('wholesale.product.show');

// Price API
Route::get('/product/{barcode}/price', [ProductDisplayController::class, 'getPrice'])->name('product.get-price');

// Category routes for retail
Route::prefix('categories')->name('categories.')->group(function () {
    Route::get('/', [CategoryController::class, 'index'])->name('index');
    Route::get('/{category}', [CategoryController::class, 'show'])->name('show');
});

// Static pages
Route::prefix('pages')->name('pages.')->group(function () {
    Route::get('/about', function () {
        return view('public.pages.about');
    })->name('about');
    
    Route::get('/contact', function () {
        return view('public.pages.contact');
    })->name('contact');
    
    Route::get('/services', function () {
        return view('public.pages.services');
    })->name('services');
});

// ============================================================================
// WHOLESALE ROUTES (Wholesale Website - https://b2b.chibobrand.com)
// ============================================================================

// Wholesale public routes (fully isolated under /b2b)
Route::prefix('b2b')->name('wholesale.')->group(function () {
    // Redirect /b2b to wholesale landing (/b2b/shop)
    Route::redirect('/', '/b2b/shop')->name('home');
    
    // Our Brand page (Wholesale)
    Route::get('/our-brand', [HomeController::class, 'ourBrandWholesale'])->name('our-brand');
    Route::get('/products', [ProductController::class, 'wholesaleIndex'])->name('products.index');
    Route::get('/products/search', [ProductController::class, 'wholesaleSearch'])->name('products.search');
    // Live autocomplete search for wholesale products
    Route::get('/products/autocomplete', [\App\Http\Controllers\ProductDisplayController::class, 'autocompleteWholesale'])->name('products.autocomplete');
    Route::get('/products/{product}', [ProductController::class, 'wholesaleShow'])->name('products.show');
    Route::get('/categories', [CategoryController::class, 'wholesaleIndex'])->name('categories.index');
    Route::get('/categories/{category}', [CategoryController::class, 'wholesaleShow'])->name('categories.show');
    Route::get('/cart', [CartController::class, 'wholesaleIndex'])->name('cart');
    Route::get('/checkout', [CartController::class, 'wholesaleCheckout'])->name('checkout');
    Route::post('/checkout', [CartController::class, 'processCheckout'])->name('checkout.process');

    // Wholesale static pages (channel-specific)
    Route::get('/about', function () { return view('wholesale.pages.about'); })->name('about');
    Route::get('/services', function () { 
        $heroSlides = \App\Models\HeroSlide::active()->forWholesaleServices()->ordered()->get();
        return view('wholesale.pages.services', compact('heroSlides')); 
    })->name('services');
    Route::get('/contact', function () { return view('wholesale.pages.contact'); })->name('contact');
});

// ============================================================================
// CUSTOMER ROUTES (Customer Authentication & Dashboard)
// ============================================================================

// Customer authentication routes
Route::prefix('customer')->name('customer.')->group(function () {
    // Registration routes (keep these for customer registration)
    Route::middleware('guest:customer')->group(function () {
        Route::get('/register', [CustomerController::class, 'showRegistrationForm'])->name('register');
        Route::post('/register', [CustomerController::class, 'register']);
    });
    
    // Customer logout route (outside auth middleware)
    Route::post('/logout', [CustomerController::class, 'logout'])->name('logout');
    
    // Protected customer routes (using customer guard)
    Route::middleware('auth:customer')->group(function () {
        Route::get('/dashboard', [CustomerController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [CustomerController::class, 'profile'])->name('profile');
        Route::put('/profile', [CustomerController::class, 'updateProfile'])->name('profile.update');
        Route::get('/change-password', [CustomerController::class, 'showChangePasswordForm'])->name('change-password');
        Route::post('/change-password', [CustomerController::class, 'changePassword']);
        
        // Product routes (retail or wholesale based on customer type)
        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/', [CustomerController::class, 'products'])->name('index');
            Route::get('/search', [CustomerController::class, 'searchProducts'])->name('search');
            Route::get('/{product}', [CustomerController::class, 'showProduct'])->name('show');
        });
        
        // Category routes (retail or wholesale based on customer type)
        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/', [CustomerController::class, 'categories'])->name('index');
            Route::get('/{category}', [CustomerController::class, 'showCategory'])->name('show');
        });
        
        // Order routes
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [OrderController::class, 'customerOrders'])->name('index');
            Route::get('/{order}', [OrderController::class, 'customerShow'])->name('show');
            Route::post('/', [OrderController::class, 'store'])->name('store');
        });
    });
});

// ============================================================================
// CONTEXT-AWARE CUSTOMER ROUTES
// ============================================================================

// Wholesale customer routes (B2B context)
Route::prefix('b2b/customer')->name('b2b.customer.')->group(function () {
    Route::middleware('auth:customer')->group(function () {
        Route::get('/dashboard', [CustomerController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [CustomerController::class, 'profile'])->name('profile');
        Route::put('/profile', [CustomerController::class, 'updateProfile'])->name('profile.update');
        Route::get('/change-password', [CustomerController::class, 'showChangePasswordForm'])->name('change-password');
        Route::post('/change-password', [CustomerController::class, 'changePassword']);
        
        // Product routes (wholesale context)
        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/', [CustomerController::class, 'products'])->name('index');
            Route::get('/search', [CustomerController::class, 'searchProducts'])->name('search');
            Route::get('/{product}', [CustomerController::class, 'showProduct'])->name('show');
        });
        
        // Category routes (wholesale context)
        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/', [CustomerController::class, 'categories'])->name('index');
            Route::get('/{category}', [CustomerController::class, 'showCategory'])->name('show');
        });
        
        // Order routes
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [OrderController::class, 'customerOrders'])->name('index');
            Route::get('/{order}', [OrderController::class, 'customerShow'])->name('show');
            Route::post('/', [OrderController::class, 'store'])->name('store');
        });
        
        // Logout route
        Route::post('/logout', [CustomerController::class, 'logout'])->name('logout');
    });
});

// Retail customer routes (Retail context)
Route::prefix('customer')->name('retail.customer.')->group(function () {
    Route::middleware('auth:customer')->group(function () {
        Route::get('/dashboard', [CustomerController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [CustomerController::class, 'profile'])->name('profile');
        Route::put('/profile', [CustomerController::class, 'updateProfile'])->name('profile.update');
        Route::get('/change-password', [CustomerController::class, 'showChangePasswordForm'])->name('change-password');
        Route::post('/change-password', [CustomerController::class, 'changePassword']);
        
        // Product routes (retail context)
        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/', [CustomerController::class, 'products'])->name('index');
            Route::get('/search', [CustomerController::class, 'searchProducts'])->name('search');
            Route::get('/{product}', [CustomerController::class, 'showProduct'])->name('show');
        });
        
        // Category routes (retail context)
        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/', [CustomerController::class, 'categories'])->name('index');
            Route::get('/{category}', [CustomerController::class, 'showCategory'])->name('show');
        });
        
        // Order routes
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [OrderController::class, 'customerOrders'])->name('index');
            Route::get('/{order}', [OrderController::class, 'customerShow'])->name('show');
            Route::post('/', [OrderController::class, 'store'])->name('store');
        });
        
        // Logout route
        Route::post('/logout', [CustomerController::class, 'logout'])->name('logout');
    });
});

// ============================================================================
// PUBLIC TEST ROUTES (No authentication required)
// ============================================================================

// Public test route for debugging
Route::get('offers/public-test', function() {
    return response()->json(['status' => 'ok', 'message' => 'Public route accessible']);
});

// ============================================================================
// ADMIN ROUTES
// ============================================================================

Route::prefix('admin')->name('admin.')->group(function () {
    // Admin logout route (login is handled by unified AuthController)
    Route::post('/logout', [AdminController::class, 'logout'])->name('logout');

    // Protected admin routes
    Route::middleware(['auth', 'admin'])->group(function () {
                // Dashboard
                Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
            Route::get('/dashboard/monthly-orders', [AdminController::class, 'getMonthlyOrdersData'])->name('dashboard.monthly-orders');
                Route::get('/demo', function () {
                    return view('admin.demo');
                })->name('demo');
        
        // Settings
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::put('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
        Route::post('/settings/toggle-2fa', [AdminController::class, 'toggle2fa'])->name('settings.toggle2fa');
        
        // Settings Routes (super_admin only)
        Route::middleware('admin.role:super_admin')->prefix('settings')->name('settings.')->group(function () {
            Route::get('/sms', [App\Http\Controllers\Admin\SettingsController::class, 'smsSettings'])->name('sms');
            Route::post('/sms', [App\Http\Controllers\Admin\SettingsController::class, 'updateSmsSettings'])->name('sms.update');
            Route::post('/sms/test', [App\Http\Controllers\Admin\SettingsController::class, 'testSms'])->name('sms.test');
            Route::get('/sms/diagnose', [App\Http\Controllers\Admin\SettingsController::class, 'diagnoseSms'])->name('sms.diagnose');
            Route::get('/customer-sources', [\App\Http\Controllers\Admin\CustomerSourceController::class, 'index'])->name('customer-sources.index');
            Route::post('/customer-sources', [\App\Http\Controllers\Admin\CustomerSourceController::class, 'store'])->name('customer-sources.store');
            Route::post('/customer-sources/{source}/toggle', [\App\Http\Controllers\Admin\CustomerSourceController::class, 'toggle'])->name('customer-sources.toggle');
            Route::delete('/customer-sources/{source}', [\App\Http\Controllers\Admin\CustomerSourceController::class, 'destroy'])->name('customer-sources.destroy');
        });

        // JSON helpers for lead source sub-dropdowns
        Route::get('/api/campaigns-list', function () {
            return response()->json(\App\Models\Campaign::select('id', 'title')->orderBy('title')->get());
        })->name('api.campaigns-list');
        Route::get('/api/programs-list', function () {
            return response()->json(\App\Models\SalesProgram::active()->select('id', 'name')->get());
        })->name('api.programs-list');

        // Advanced Bulk SMS Management Routes
        Route::prefix('bulk-sms')->name('bulk-sms.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\BulkSmsController::class, 'index'])->name('index');
            Route::get('/customers', [\App\Http\Controllers\Admin\BulkSmsController::class, 'getCustomers'])->name('customers');
            Route::post('/preview', [\App\Http\Controllers\Admin\BulkSmsController::class, 'preview'])->name('preview');
            Route::post('/send', [\App\Http\Controllers\Admin\BulkSmsController::class, 'send'])->name('send');
            Route::get('/stats', [\App\Http\Controllers\Admin\BulkSmsController::class, 'getStats'])->name('stats');
            Route::get('/auto-settings', [\App\Http\Controllers\Admin\BulkSmsController::class, 'getAutoSettings'])->name('auto-settings.get');
            Route::post('/auto-settings', [\App\Http\Controllers\Admin\BulkSmsController::class, 'saveAutoSettings'])->name('auto-settings.save');
            Route::get('/balance', [\App\Http\Controllers\Admin\BulkSmsController::class, 'getBalance'])->name('balance');
            Route::post('/test-send', [\App\Http\Controllers\Admin\BulkSmsController::class, 'testSend'])->name('test-send');
        });
        
        // Admin Management
        Route::get('/admins', [AdminController::class, 'adminsIndex'])->name('admins.index');
        Route::get('/admins/create', [AdminController::class, 'createAdminPage'])->name('admins.create');
        Route::get('/admins/{id}/edit', [AdminController::class, 'editAdminPage'])->name('admins.edit');
        Route::get('/admins/{id}', [AdminController::class, 'showAdmin'])->name('admins.show');
        Route::post('/admins', [AdminController::class, 'storeAdmin'])->name('admins.store');
        Route::put('/admins/{id}', [AdminController::class, 'updateAdmin'])->name('admins.update');
        Route::delete('/admins/{id}', [AdminController::class, 'destroyAdmin'])->name('admins.destroy');
        Route::post('/admins/{id}/pay-salary', [AdminController::class, 'paySalary'])->name('admins.pay-salary');
        Route::post('/admins/pay-all', [AdminController::class, 'payAllSalaries'])->name('admins.pay-all');
        
        // Roles & Permissions Configuration (Only for super_admin)
        Route::get('/roles-permissions', [App\Http\Controllers\Admin\RolesPermissionsController::class, 'index'])->name('roles-permissions.index');
        Route::put('/roles-permissions/{role}', [App\Http\Controllers\Admin\RolesPermissionsController::class, 'update'])->name('roles-permissions.update');
        
        // Profile
        Route::get('/profile', [AdminController::class, 'profile'])->name('profile');
        Route::put('/profile', [AdminController::class, 'updateProfile'])->name('profile.update');
        Route::put('/profile/password', [AdminController::class, 'updatePassword'])->name('profile.password');
        Route::put('/profile/preferences', [AdminController::class, 'updatePreferences'])->name('profile.preferences');
        
        // Hero Slides Management
        Route::resource('hero-slides', App\Http\Controllers\Admin\HeroSlideController::class);
        Route::post('/hero-slides/update-sort-order', [App\Http\Controllers\Admin\HeroSlideController::class, 'updateSortOrder'])->name('hero-slides.update-sort-order');
        Route::get('/hero-slides/{heroSlide}/track-click', [App\Http\Controllers\Admin\HeroSlideController::class, 'trackClick'])->name('hero-slides.track-click');
        Route::get('/hero-slides/{heroSlide}/track-impression', [App\Http\Controllers\Admin\HeroSlideController::class, 'trackImpression'])->name('hero-slides.track-impression');
        Route::get('/hero-slides-analytics', [App\Http\Controllers\Admin\HeroSlideController::class, 'analytics'])->name('hero-slides.analytics');
        
        // Storage Setup (for shared hosting)
        Route::get('/storage-setup', [App\Http\Controllers\StorageSetupController::class, 'index'])->name('storage.setup');
        Route::post('/storage-setup/create-symlink', [App\Http\Controllers\StorageSetupController::class, 'createSymlink'])->name('storage.create-symlink');
        Route::post('/storage-setup/test', [App\Http\Controllers\StorageSetupController::class, 'testStorage'])->name('storage.test');
        Route::post('/storage-setup/run-artisan', [App\Http\Controllers\StorageSetupController::class, 'runArtisanCommand'])->name('storage.run-artisan');
        
        // Sales Programs (Inside Programs admin)
        Route::prefix('sales/programs')->name('sales.programs.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\SalesProgramController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Admin\SalesProgramController::class, 'store'])->name('store');
            Route::put('/{program}', [\App\Http\Controllers\Admin\SalesProgramController::class, 'update'])->name('update');
            Route::delete('/{program}', [\App\Http\Controllers\Admin\SalesProgramController::class, 'destroy'])->name('destroy');
        });

        // Reports
        Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
        Route::get('/reports/daily', [AdminController::class, 'dailyReport'])->name('reports.daily');
        Route::get('/reports/monthly', [AdminController::class, 'monthlyReport'])->name('reports.monthly');
        Route::get('/reports/design-tasks', [AdminController::class, 'designTasksReport'])->name('reports.design-tasks');
        Route::get('/reports/design-tasks/export', [AdminController::class, 'exportDesignTasksReport'])->name('reports.design-tasks.export');
        Route::get('/reports/operators', [App\Http\Controllers\Admin\OperatorPerformanceController::class, 'index'])->name('reports.operators');
        Route::get('/reports/operators/export', [App\Http\Controllers\Admin\OperatorPerformanceController::class, 'exportCsv'])->name('reports.operators.export');
        Route::get('/reports/operators/print', [App\Http\Controllers\Admin\OperatorPerformanceController::class, 'print'])->name('reports.operators.print');
        Route::get('/reports/operators/pdf', [App\Http\Controllers\Admin\OperatorPerformanceController::class, 'exportPdf'])->name('reports.operators.pdf');
        Route::get('/reports/designer-analytics/{id}', [AdminController::class, 'designerAnalytics'])->name('reports.designer-analytics');
        
        // Security - Audit Logs
        Route::get('/audit-logs', [App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('audit-logs.index');
        
        // Security - Password Reset (Super Admin Only - checked in controller)
        Route::get('/security/reset-password', [App\Http\Controllers\Admin\SecurityController::class, 'showResetPasswordForm'])->name('security.reset-password');
        Route::post('/security/reset-password', [App\Http\Controllers\Admin\SecurityController::class, 'resetPassword'])->name('security.reset-password.store');
        Route::get('/security/search-users', [App\Http\Controllers\Admin\SecurityController::class, 'searchUsers'])->name('security.search-users');
        
        // Order Management
        Route::get('/orders', [AdminController::class, 'orders'])->name('orders.index');
        Route::get('/orders/{order_code}', [AdminController::class, 'viewOrder'])->name('orders.show');
        Route::post('/orders/{order_code}/approve', [AdminController::class, 'approveOrder'])->name('orders.approve');
        Route::post('/orders/{order_code}/cancel', [AdminController::class, 'cancelOrder'])->name('orders.cancel');
        Route::post('/orders/{order_code}/convert', [AdminController::class, 'convertToInvoice'])->name('orders.convert');
        Route::get('/orders/{order_code}/edit', [AdminController::class, 'editOrder'])->name('orders.edit');
        Route::put('/orders/{order_code}', [AdminController::class, 'updateOrder'])->name('orders.update');
        Route::delete('/orders/{order_code}', [AdminController::class, 'destroyOrder'])->name('orders.destroy');
        Route::delete('/orders', [AdminController::class, 'destroyAllOrders'])->name('orders.destroy-all');
        
        // Notifications Management
        Route::get('/notifications', [AdminController::class, 'notifications'])->name('notifications.index');
        Route::get('/notifications/refresh-count', [AdminController::class, 'getNotificationCount'])->name('notifications.refresh-count');
        Route::get('/notifications/{notification}', [AdminController::class, 'showNotification'])->name('notifications.show');
        Route::post('/notifications/{notification}/read', [AdminController::class, 'markNotificationRead'])->name('notifications.read');
        Route::post('/notifications/mark-all-read', [AdminController::class, 'markAllNotificationsRead'])->name('notifications.mark-all-read');
        Route::delete('/notifications/{notification}', [AdminController::class, 'deleteNotification'])->name('notifications.delete');
        
        // Contact Messages Management
        Route::resource('contact-messages', ContactMessageController::class)->names([
            'index' => 'contact-messages.index',
            'show' => 'contact-messages.show',
            'update' => 'contact-messages.update',
            'destroy' => 'contact-messages.destroy',
        ]);
        Route::post('contact-messages/{id}/send-email', [ContactMessageController::class, 'sendEmailReply'])->name('contact-messages.send-email');
        
        // Message Templates Management
        Route::resource('message-templates', MessageTemplateController::class)->names([
            'index' => 'message-templates.index',
            'store' => 'message-templates.store',
            'update' => 'message-templates.update',
            'destroy' => 'message-templates.destroy',
        ]);
        Route::post('message-templates/send', [MessageTemplateController::class, 'send'])->name('message-templates.send');
        Route::post('message-templates/broadcast-leads', [MessageTemplateController::class, 'broadcastToLeads'])->name('message-templates.broadcast-leads');
        
        // Design Tasks Management
        Route::get('design-tasks/{designTask}/print-invoice', [DesignTaskController::class, 'printInvoice'])->name('design-tasks.print-invoice');
        Route::get('design-tasks/customer/{customerId}/print-invoice', [DesignTaskController::class, 'printCustomerInvoice'])->name('design-tasks.print-customer-invoice');
        Route::get('design-tasks/print-filtered', [DesignTaskController::class, 'printFiltered'])->name('design-tasks.print-filtered');
        Route::get('design-tasks/invoice-data/{id}', [DesignTaskController::class, 'getInvoiceData'])->name('design-tasks.invoice-data');
        Route::get('design-tasks/task-data/{id}', [DesignTaskController::class, 'getTaskData'])->name('design-tasks.task-data');
        Route::get('design-tasks/reports', [\App\Http\Controllers\Admin\DesignTaskReportController::class, 'index'])->name('design-tasks.reports');
        Route::get('design-tasks/reports/print', [\App\Http\Controllers\Admin\DesignTaskReportController::class, 'print'])->name('design-tasks.reports.print');
        Route::get('design-tasks/reports/pdf', [\App\Http\Controllers\Admin\DesignTaskReportController::class, 'pdf'])->name('design-tasks.reports.pdf');
        Route::get('design-tasks/reports/excel', [\App\Http\Controllers\Admin\DesignTaskReportController::class, 'excel'])->name('design-tasks.reports.excel');
        Route::get('design-tasks/paid', [DesignTaskController::class, 'paid'])->name('design-tasks.paid');
        Route::get('design-tasks/pending', [DesignTaskController::class, 'pending'])->name('design-tasks.pending');
        Route::get('design-tasks/invoices', [DesignTaskController::class, 'invoices'])->name('design-tasks.invoices');
        Route::get('design-tasks/customer-tasks/{customerId}', [DesignTaskController::class, 'getCustomerTasks'])->name('design-tasks.customer-tasks');
        Route::get('design-tasks/customer-businesses/{customerId}', [DesignTaskController::class, 'getCustomerBusinesses'])->name('design-tasks.customer-businesses');
        Route::post('design-tasks/check-duplicate', [DesignTaskController::class, 'checkDuplicate'])->name('design-tasks.check-duplicate');

        // Design Tasks Trash & Recycling Routes
        Route::get('design-tasks/trash', [DesignTaskController::class, 'trash'])->name('design-tasks.trash');
        Route::post('design-tasks/{id}/restore', [DesignTaskController::class, 'restore'])->name('design-tasks.restore');
        Route::delete('design-tasks/{id}/force-delete', [DesignTaskController::class, 'forceDelete'])->name('design-tasks.force-delete');
        Route::delete('design-tasks/empty-trash', [DesignTaskController::class, 'emptyTrash'])->name('design-tasks.empty-trash');

        // Design Task Types
        Route::resource('design-task-types', \App\Http\Controllers\Admin\DesignTaskTypeController::class);

        Route::resource('design-tasks', DesignTaskController::class)->names([
            'index' => 'design-tasks.index',
            'create' => 'design-tasks.create',
            'store' => 'design-tasks.store',
            'show' => 'design-tasks.show',
        ]);
        
        Route::post('design-tasks/{designTask}/assign', [DesignTaskController::class, 'assign'])->name('design-tasks.assign');
        Route::post('design-tasks/{designTask}/assign-delivery', [DesignTaskController::class, 'assignDelivery'])->name('design-tasks.assign-delivery');
        Route::post('design-tasks/{designTask}/delivery-status', [DesignTaskController::class, 'updateDeliveryStatus'])->name('design-tasks.update-delivery-status');
        Route::post('design-tasks/{designTask}/status', [DesignTaskController::class, 'updateStatus'])->name('design-tasks.update-status');
        Route::post('design-tasks/{designTask}/mark-loss', [DesignTaskController::class, 'markAsLoss'])->name('design-tasks.mark-loss');
        Route::post('design-tasks/{designTask}/cancel', [DesignTaskController::class, 'cancel'])->name('design-tasks.cancel');
        Route::post('design-tasks/{designTask}/comment', [DesignTaskController::class, 'addComment'])->name('design-tasks.comment');
        
        // Design Task Payment Management
        Route::put('design-tasks/{designTask}/update-payment', [DesignTaskController::class, 'updatePayment'])->name('design-tasks.update-payment');
        
        // Design Task Delivery Notification
        Route::post('design-tasks/{designTask}/send-delivery-notification', [DesignTaskController::class, 'sendDeliveryNotification'])->name('design-tasks.send-delivery-notification');
        
        // Enhanced Product management (full resource restored)
    // Offers management for enhanced products (MUST come before resource routes)
    Route::get('enhanced-products/offers', [EnhancedProductController::class, 'offers'])->name('enhanced-products.offers');
    Route::get('enhanced-products/offers/{product}', [EnhancedProductController::class, 'offers'])->name('enhanced-products.offers.product');
    
    // Sort order management
    Route::post('enhanced-products/update-sort-order', [EnhancedProductController::class, 'updateSortOrder'])->name('enhanced-products.update-sort-order');
    Route::post('enhanced-products/fix-sort-order', [EnhancedProductController::class, 'fixSortOrder'])->name('enhanced-products.fix-sort-order');
        
        // Offer CRUD operations
        Route::post('offers', [App\Http\Controllers\OfferController::class, 'store'])->name('offers.store');
        Route::get('offers/product/{productBarcode}', [App\Http\Controllers\OfferController::class, 'getProductOffers'])->name('offers.product');
        Route::put('offers/{id}', [App\Http\Controllers\OfferController::class, 'update'])->name('offers.update');
        Route::delete('offers/{id}', [App\Http\Controllers\OfferController::class, 'destroy'])->name('offers.destroy');
        
        // Debug route for testing
        Route::get('offers/test', function() {
            return response()->json(['status' => 'ok', 'message' => 'Offers controller is accessible']);
        })->name('offers.test');
        
        
        // Temporary debug route for edit form (must come before resource route)
        Route::put('enhanced-products/{barcode}', function (\Illuminate\Http\Request $request, $barcode) {
            \Log::info('EDIT FORM SUBMISSION DEBUG', [
                'method' => $request->method(),
                'url' => $request->url(),
                'barcode' => $barcode,
                'data' => $request->all(),
                'headers' => $request->headers->all()
            ]);
            return response()->json(['status' => 'success', 'message' => 'Edit form received']);
        })->name('enhanced-products.update-debug');
        
        Route::resource('enhanced-products', EnhancedProductController::class)->names([
            'index' => 'enhanced-products.index',
            'create' => 'enhanced-products.create',
            'store' => 'enhanced-products.store',
            'show' => 'enhanced-products.show',
            'edit' => 'enhanced-products.edit',
            'update' => 'enhanced-products.update',
            'destroy' => 'enhanced-products.destroy',
        ])->parameters(['enhanced-products' => 'barcode']);
        
        // Toggle visibility for enhanced products
        Route::post('enhanced-products/{product}/toggle-visibility', [EnhancedProductController::class, 'toggleVisibility'])->name('enhanced-products.toggle-visibility');

        // Adjust quantity routes
        Route::get('enhanced-products/{barcode}/quantity', [EnhancedProductController::class, 'editQuantity'])->name('enhanced-products.quantity.edit');
        Route::post('enhanced-products/{barcode}/quantity', [EnhancedProductController::class, 'updateQuantity'])->name('enhanced-products.quantity.update');
        
// Remove legacy admin.products routes during rebuild

// Test route for form submission debugging
Route::post('/admin/test-form-submission', function(\Illuminate\Http\Request $request) {
    \Log::info('=== TEST FORM SUBMISSION RECEIVED ===');
    \Log::info('Request method:', $request->method());
    \Log::info('Request data:', $request->all());
    \Log::info('Request files:', $request->allFiles());
    return response()->json(['status' => 'success', 'message' => 'Form received successfully']);
})->name('test.form.submission');
        Route::delete('/enhanced-products/images/{image}', [EnhancedProductController::class, 'deleteImage'])->name('enhanced-products.delete-image');
        Route::get('/enhanced-products/{id}/price', [EnhancedProductController::class, 'getPrice'])->name('enhanced-products.get-price');
        Route::get('/enhanced-products/generate-barcode', [EnhancedProductController::class, 'generateBarcode'])->name('enhanced-products.generate-barcode');
        
        // Test route for debugging
        Route::post('/test-form', function(\Illuminate\Http\Request $request) {
            \Log::info('Test form received', $request->all());
            return response()->json(['status' => 'success', 'data' => $request->all()]);
        });
        
        // Category management
        Route::get('/categories', [CategoryController::class, 'adminIndex'])->name('categories.index');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{category}', [CategoryController::class, 'adminShow'])->name('categories.show');
        Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        Route::put('/categories/{category}/status', [CategoryController::class, 'updateStatus'])->name('categories.status.update');
        
        // Order management
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [OrderController::class, 'index'])->name('index');
            Route::get('/{order}', [OrderController::class, 'show'])->name('show');
            Route::put('/{order}/status', [OrderController::class, 'updateStatus'])->name('status.update');
            Route::post('/{order}/approve', [OrderController::class, 'approve'])->name('approve');
            Route::post('/{order}/cancel', [OrderController::class, 'cancel'])->name('cancel');
            Route::get('/daily-summary', [OrderController::class, 'dailySummary'])->name('daily-summary');
        });

        // POS Terminal
        Route::prefix('pos')->name('pos.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\POSController::class, 'index'])->name('index');
            Route::get('/products/search', [App\Http\Controllers\Admin\POSController::class, 'searchProducts'])->name('products.search');
            Route::get('/customers/search', [App\Http\Controllers\Admin\POSController::class, 'searchCustomers'])->name('customers.search');
            Route::post('/customers/store', [App\Http\Controllers\Admin\POSController::class, 'storeCustomer'])->name('customers.store');
            Route::put('/customers/{customer}', [App\Http\Controllers\Admin\POSController::class, 'updateCustomer'])->name('customers.update');
            Route::post('/store', [App\Http\Controllers\Admin\POSController::class, 'storeOrder'])->name('store');
        });

        // Saler Performance (accessible by admins, operators, receptionists, and salers)
        Route::prefix('saler-performance')->name('saler-performance.')->middleware(['admin.role:admin,operator,receptionist,saler,accountant'])->group(function () {
            Route::get('/', [SalerPerformanceController::class, 'index'])->name('index');
            Route::get('/export', [SalerPerformanceController::class, 'export'])->name('export');
            Route::get('/chart-data', [SalerPerformanceController::class, 'getChartData'])->name('chart-data');
            Route::get('/print', [SalerPerformanceController::class, 'print'])->name('print');
            Route::get('/{id}', [SalerPerformanceController::class, 'show'])->name('show');
        });

        // Gatekeeper Performance (accessible by admins, operators, receptionists)
        Route::prefix('gatekeeper-performance')->name('gatekeeper-performance.')->middleware(['admin.role:admin,operator,receptionist,accountant'])->group(function () {
            Route::get('/', [App\Http\Controllers\GatekeeperPerformanceController::class, 'index'])->name('index');
            Route::get('/export', [App\Http\Controllers\GatekeeperPerformanceController::class, 'export'])->name('export');
        });

        // Delivery Performance (accessible by admins, operators, receptionists)
        Route::prefix('delivery-performance')->name('delivery-performance.')->middleware(['admin.role:admin,operator,receptionist,accountant'])->group(function () {
            Route::get('/', [App\Http\Controllers\DeliveryPerformanceController::class, 'index'])->name('index');
            Route::get('/export', [App\Http\Controllers\DeliveryPerformanceController::class, 'export'])->name('export');
        });

        // Saler Dashboard (for salespeople only)
        Route::middleware(['admin.role:saler'])->group(function () {
            Route::get('/my-dashboard', [App\Http\Controllers\SalerDashboardController::class, 'myDashboard'])->name('saler.my-dashboard');
            Route::get('/saler-sales-report', [App\Http\Controllers\SalerSalesReportController::class, 'index'])->name('saler.sales-report');
            Route::get('/saler-sales-report/print', [App\Http\Controllers\SalerSalesReportController::class, 'print'])->name('saler.sales-report.print');
            Route::get('/saler-sales-report/pdf', [App\Http\Controllers\SalerSalesReportController::class, 'pdf'])->name('saler.sales-report.pdf');
            Route::get('/saler-sales-report/excel', [App\Http\Controllers\SalerSalesReportController::class, 'excel'])->name('saler.sales-report.excel');
        });

        // Delivery Management Routes (for delivery users)
        Route::prefix('delivery')->name('delivery.')->group(function () {
            Route::get('/incoming', [AdminController::class, 'deliveryIncoming'])->name('incoming');
            Route::get('/search', [AdminController::class, 'deliverySearch'])->name('search');
            Route::post('/pull/{designTask}', [AdminController::class, 'deliveryPull'])->name('pull');
            Route::get('/completed', [AdminController::class, 'deliveryCompleted'])->name('completed');
            Route::get('/canceled', [AdminController::class, 'deliveryCanceled'])->name('canceled');
            Route::get('/updates', [AdminController::class, 'deliveryUpdates'])->name('updates');
            Route::get('/print-filtered', [AdminController::class, 'printDeliveryTasks'])->name('print-filtered');
        });
        
        // Payment Management
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/paid', [App\Http\Controllers\Admin\PaymentController::class, 'paid'])->name('paid');
            Route::get('/pending', [App\Http\Controllers\Admin\PaymentController::class, 'pending'])->name('pending');
            Route::get('/invoices', [App\Http\Controllers\Admin\PaymentController::class, 'invoices'])->name('invoices');
            Route::get('/order-data/{id}', [App\Http\Controllers\Admin\PaymentController::class, 'getOrderData'])->name('order-data');
            Route::put('/{id}/update', [App\Http\Controllers\Admin\PaymentController::class, 'updatePayment'])->name('update');
        });
        
        // Leads Management
        Route::prefix('leads')->name('leads.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\LeadController::class, 'index'])->name('index');
            Route::get('/overdue', [\App\Http\Controllers\Admin\LeadController::class, 'overdue'])->name('overdue');
            Route::get('/print', [\App\Http\Controllers\Admin\LeadController::class, 'print'])->name('print');
            Route::get('/pdf', [\App\Http\Controllers\Admin\LeadController::class, 'exportPdf'])->name('pdf');
            Route::get('/excel', [\App\Http\Controllers\Admin\LeadController::class, 'exportExcel'])->name('excel');
            Route::post('/', [\App\Http\Controllers\Admin\LeadController::class, 'store'])->name('store');
            Route::put('/{lead}', [\App\Http\Controllers\Admin\LeadController::class, 'update'])->name('update');
            Route::patch('/{lead}/quick-edit', [\App\Http\Controllers\Admin\LeadController::class, 'quickEdit'])->name('quick-edit');
            Route::patch('/{lead}/update-follow-up', [\App\Http\Controllers\Admin\LeadController::class, 'updateFollowUp'])->name('update-follow-up');
            Route::post('/{lead}/follow-up', [\App\Http\Controllers\Admin\LeadController::class, 'addFollowUp'])->name('follow-up');
        });

        // Sales Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/sales', [\App\Http\Controllers\Admin\SalesReportController::class, 'index'])->name('sales');
            Route::get('/sales/pdf', [\App\Http\Controllers\Admin\SalesReportController::class, 'exportPdf'])->name('sales.pdf');
            Route::get('/sales/print', [\App\Http\Controllers\Admin\SalesReportController::class, 'print'])->name('sales.print');
            Route::get('/sales/excel', [\App\Http\Controllers\Admin\SalesReportController::class, 'exportExcel'])->name('sales.excel');
            Route::get('/department-sales', [\App\Http\Controllers\Admin\DepartmentSalesReportController::class, 'index'])->name('department_sales');
            Route::get('/department-sales/print', [\App\Http\Controllers\Admin\DepartmentSalesReportController::class, 'print'])->name('department_sales.print');
            Route::get('/department-sales/pdf', [\App\Http\Controllers\Admin\DepartmentSalesReportController::class, 'pdf'])->name('department_sales.pdf');
            Route::get('/department-sales/excel', [\App\Http\Controllers\Admin\DepartmentSalesReportController::class, 'excel'])->name('department_sales.excel');
            Route::get('/customers', [\App\Http\Controllers\Admin\CustomerReportController::class, 'index'])->name('customers');
            Route::get('/customers/print', [\App\Http\Controllers\Admin\CustomerReportController::class, 'print'])->name('customers.print');
            Route::get('/customers/pdf', [\App\Http\Controllers\Admin\CustomerReportController::class, 'pdf'])->name('customers.pdf');
            Route::get('/customers/excel', [\App\Http\Controllers\Admin\CustomerReportController::class, 'excel'])->name('customers.excel');
        });

        // HR Module
        Route::prefix('hr')->name('hr.')->group(function () {
            // Index + store (non-wildcard)
            Route::get('/', [\App\Http\Controllers\Admin\HRController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Admin\HRController::class, 'store'])->name('store');

            // Static sub-routes MUST come before /{employee} wildcard
            Route::get('/attendance/daily', [\App\Http\Controllers\Admin\HRController::class, 'attendance'])->name('attendance');
            Route::post('/attendance/bulk', [\App\Http\Controllers\Admin\HRController::class, 'bulkAttendance'])->name('attendance.bulk');
            Route::post('/attendance/single', [\App\Http\Controllers\Admin\HRController::class, 'storeAttendance'])->name('attendance.store');
            Route::get('/attendance/report', [\App\Http\Controllers\Admin\HRController::class, 'attendanceReport'])->name('attendance.report');
            Route::get('/attendance/report/print', [\App\Http\Controllers\Admin\HRController::class, 'attendanceReportPrint'])->name('attendance.report.print');
            Route::get('/attendance/report/pdf', [\App\Http\Controllers\Admin\HRController::class, 'attendanceReportPdf'])->name('attendance.report.pdf');
            Route::get('/attendance/report/excel', [\App\Http\Controllers\Admin\HRController::class, 'attendanceReportExcel'])->name('attendance.report.excel');

            Route::get('/leaves', [\App\Http\Controllers\Admin\HRController::class, 'leaves'])->name('leaves');
            Route::post('/leaves', [\App\Http\Controllers\Admin\HRController::class, 'storeLeave'])->name('leaves.store');
            Route::put('/leaves/{leave}', [\App\Http\Controllers\Admin\HRController::class, 'updateLeave'])->name('leaves.update');
            Route::put('/leaves/{leave}/approve', [\App\Http\Controllers\Admin\HRController::class, 'approveLeave'])->name('leaves.approve');
            Route::put('/leaves/{leave}/reject', [\App\Http\Controllers\Admin\HRController::class, 'rejectLeave'])->name('leaves.reject');

            Route::get('/kpis', [\App\Http\Controllers\Admin\HRController::class, 'kpis'])->name('kpis');
            Route::post('/kpis', [\App\Http\Controllers\Admin\HRController::class, 'storeKpi'])->name('kpis.store');

            Route::get('/create', [\App\Http\Controllers\Admin\HRController::class, 'create'])->name('create');
            Route::get('/{employee}/edit', [\App\Http\Controllers\Admin\HRController::class, 'edit'])->name('edit');

            // Wildcard employee routes LAST
            Route::get('/{employee}', [\App\Http\Controllers\Admin\HRController::class, 'show'])->name('show');
            Route::put('/{employee}', [\App\Http\Controllers\Admin\HRController::class, 'update'])->name('update');
            Route::delete('/{employee}', [\App\Http\Controllers\Admin\HRController::class, 'destroy'])->name('destroy');
            Route::post('/{employee}/documents', [\App\Http\Controllers\Admin\HRController::class, 'uploadDocument'])->name('documents.store');
            Route::delete('/{employee}/documents/{document}', [\App\Http\Controllers\Admin\HRController::class, 'deleteDocument'])->name('documents.destroy');
        });

        // Finance Management
        Route::prefix('finance')->name('finance.')->group(function () {
            Route::get('/dashboard', [\App\Http\Controllers\Admin\FinanceController::class, 'dashboard'])->name('dashboard');
            Route::get('/payroll', [\App\Http\Controllers\Admin\FinanceController::class, 'payroll'])->name('payroll');
            Route::get('/payroll/print', [\App\Http\Controllers\Admin\FinanceController::class, 'payrollPrint'])->name('payroll.print');
            Route::get('/payroll/pdf', [\App\Http\Controllers\Admin\FinanceController::class, 'payrollPdf'])->name('payroll.pdf');
            Route::get('/payroll/excel', [\App\Http\Controllers\Admin\FinanceController::class, 'payrollExcel'])->name('payroll.excel');
            Route::get('/payslip/pdf/{id}', [\App\Http\Controllers\Admin\FinanceController::class, 'payslipPdf'])->name('payslip.pdf');
            Route::get('/reports', [\App\Http\Controllers\Admin\FinanceController::class, 'reports'])->name('reports');
            Route::get('/profit-loss', [\App\Http\Controllers\Admin\FinanceController::class, 'profitLoss'])->name('profit-loss');
            Route::get('/profit-loss/print', [\App\Http\Controllers\Admin\FinanceController::class, 'profitLossPrint'])->name('profit-loss.print');
            Route::get('/profit-loss/pdf', [\App\Http\Controllers\Admin\FinanceController::class, 'profitLossPdf'])->name('profit-loss.pdf');
            Route::get('/profit-loss/excel', [\App\Http\Controllers\Admin\FinanceController::class, 'profitLossExcel'])->name('profit-loss.excel');
            Route::get('/daily-report', [\App\Http\Controllers\Admin\FinanceController::class, 'dailyReport'])->name('daily-report');
            Route::get('/daily-report/pdf', [\App\Http\Controllers\Admin\FinanceController::class, 'dailyReportPdf'])->name('daily-report.pdf');
            Route::get('/balance-sheet', [\App\Http\Controllers\Admin\FinanceController::class, 'balanceSheet'])->name('balance-sheet');
            Route::get('/balance-sheet/print', [\App\Http\Controllers\Admin\FinanceController::class, 'balanceSheetPrint'])->name('balance-sheet.print');
            Route::get('/balance-sheet/pdf', [\App\Http\Controllers\Admin\FinanceController::class, 'balanceSheetPdf'])->name('balance-sheet.pdf');
            Route::get('/balance-sheet/excel', [\App\Http\Controllers\Admin\FinanceController::class, 'balanceSheetExcel'])->name('balance-sheet.excel');
            Route::get('/expenses', [\App\Http\Controllers\Admin\FinanceController::class, 'expenses'])->name('expenses');
            Route::get('/expenses/print', [\App\Http\Controllers\Admin\FinanceController::class, 'printExpenses'])->name('expenses.print');
            Route::get('/expenses/pdf', [\App\Http\Controllers\Admin\FinanceController::class, 'expensesPdf'])->name('expenses.pdf');
            Route::get('/expenses/excel', [\App\Http\Controllers\Admin\FinanceController::class, 'expensesExcel'])->name('expenses.excel');
            Route::post('/expenses', [\App\Http\Controllers\Admin\FinanceController::class, 'storeExpense'])->name('expenses.store');
            Route::put('/expenses/{id}', [\App\Http\Controllers\Admin\FinanceController::class, 'updateExpense'])->name('expenses.update');
            Route::delete('/expenses/{id}', [\App\Http\Controllers\Admin\FinanceController::class, 'destroyExpense'])->name('expenses.destroy');
            Route::delete('/payments/{id}', [\App\Http\Controllers\Admin\FinanceController::class, 'destroyPayment'])->name('payments.destroy');
            Route::get('/expenses/voucher/{id}', [\App\Http\Controllers\Admin\FinanceController::class, 'voucher'])->name('expenses.voucher');
            Route::get('/cash-flow', [\App\Http\Controllers\Admin\FinanceController::class, 'cashFlow'])->name('cash-flow');
            Route::get('/cash-flow/print', [\App\Http\Controllers\Admin\FinanceController::class, 'printCashFlow'])->name('cash-flow.print');
            Route::get('/cash-flow/pdf', [\App\Http\Controllers\Admin\FinanceController::class, 'cashFlowPdf'])->name('cash-flow.pdf');
            Route::get('/cash-flow/excel', [\App\Http\Controllers\Admin\FinanceController::class, 'cashFlowExcel'])->name('cash-flow.excel');
            Route::get('/audit', [\App\Http\Controllers\Admin\AuditController::class, 'index'])->name('audit');
            Route::get('/audit/print', [\App\Http\Controllers\Admin\AuditController::class, 'print'])->name('audit.print');
            Route::get('/audit/pdf', [\App\Http\Controllers\Admin\AuditController::class, 'pdf'])->name('audit.pdf');
            Route::get('/audit/excel', [\App\Http\Controllers\Admin\AuditController::class, 'excel'])->name('audit.excel');

            Route::get('/pending-payments', [\App\Http\Controllers\Admin\FinanceController::class, 'pendingPayments'])->name('pending-payments');
            Route::get('/pending-payments/print', [\App\Http\Controllers\Admin\FinanceController::class, 'printPendingPayments'])->name('pending-payments.print');
            Route::get('/pending-payments/pdf', [\App\Http\Controllers\Admin\FinanceController::class, 'pendingPaymentsPdf'])->name('pending-payments.pdf');
            Route::get('/pending-payments/excel', [\App\Http\Controllers\Admin\FinanceController::class, 'pendingPaymentsExcel'])->name('pending-payments.excel');
            
            // Invoices & Receipts
            Route::get('/invoices/proforma/{order_code}', [\App\Http\Controllers\Admin\InvoiceController::class, 'proforma'])->name('invoices.proforma');
            Route::get('/invoices/sales/{order_code}', [\App\Http\Controllers\Admin\InvoiceController::class, 'sales'])->name('invoices.sales');
            Route::get('/invoices/receipt/{id}', [\App\Http\Controllers\Admin\InvoiceController::class, 'receipt'])->name('invoices.receipt');
            
            // Proforma Invoice Management
            Route::get('/proforma', [\App\Http\Controllers\Admin\FinanceController::class, 'proformaIndex'])->name('proforma.index');
            Route::get('/proforma/create', [\App\Http\Controllers\Admin\InvoiceController::class, 'createProforma'])->name('proforma.create');
            Route::post('/proforma/generate', [\App\Http\Controllers\Admin\InvoiceController::class, 'generateProforma'])->name('proforma.generate');
            Route::get('/proforma/customers/search', [\App\Http\Controllers\Admin\InvoiceController::class, 'searchCustomers'])->name('proforma.customers.search');
            Route::get('/proforma/{id}/details', [\App\Http\Controllers\Admin\FinanceController::class, 'getProformaDetails'])->name('proforma.details');
            Route::get('/proforma/{id}/edit-data', [\App\Http\Controllers\Admin\FinanceController::class, 'getProformaEditData'])->name('proforma.edit-data');
            Route::match(['POST', 'PUT'], '/proforma/{id}/update', [\App\Http\Controllers\Admin\FinanceController::class, 'updateProforma'])->name('proforma.update');
            Route::post('/proforma/{id}/convert-to-tasks', [\App\Http\Controllers\Admin\FinanceController::class, 'convertProformaToTasks'])->name('proforma.convert-to-tasks');
            
            // Department Management
            Route::resource('departments', \App\Http\Controllers\Admin\DepartmentController::class)->names('departments');
            
            // Payment Requests
            Route::get('/payment-requests', [\App\Http\Controllers\Admin\PaymentRequestController::class, 'index'])->name('payment-requests.index');
            Route::get('/payment-requests/print', [\App\Http\Controllers\Admin\PaymentRequestController::class, 'print'])->name('payment-requests.print');
            Route::get('/payment-requests/pdf', [\App\Http\Controllers\Admin\PaymentRequestController::class, 'exportPdf'])->name('payment-requests.pdf');
            Route::get('/payment-requests/excel', [\App\Http\Controllers\Admin\PaymentRequestController::class, 'exportExcel'])->name('payment-requests.excel');
            Route::post('/payment-requests', [\App\Http\Controllers\Admin\PaymentRequestController::class, 'store'])->name('payment-requests.store');
            Route::put('/payment-requests/{paymentRequest}/status', [\App\Http\Controllers\Admin\PaymentRequestController::class, 'updateStatus'])->name('payment-requests.status');

            // Reconciliation
            Route::get('/reconciliation',                [\App\Http\Controllers\Admin\FinanceReconciliationController::class, 'index'])->name('reconciliation.index');
            Route::get('/reconciliation/create',         [\App\Http\Controllers\Admin\FinanceReconciliationController::class, 'create'])->name('reconciliation.create');
            Route::post('/reconciliation',               [\App\Http\Controllers\Admin\FinanceReconciliationController::class, 'store'])->name('reconciliation.store');
            Route::get('/reconciliation/{id}',           [\App\Http\Controllers\Admin\FinanceReconciliationController::class, 'show'])->name('reconciliation.show');
            Route::post('/reconciliation/fix-mismatches',[\App\Http\Controllers\Admin\FinanceReconciliationController::class, 'fixMismatches'])->name('reconciliation.fix-mismatches');
            Route::get('/reconciliation/customers/search',[\App\Http\Controllers\Admin\FinanceReconciliationController::class, 'searchCustomers'])->name('reconciliation.customers.search');
            Route::get('/reconciliation/tasks/search',   [\App\Http\Controllers\Admin\FinanceReconciliationController::class, 'searchTasks'])->name('reconciliation.tasks.search');

            // Verification Dashboard
            Route::get('/verification-dashboard', [\App\Http\Controllers\Admin\FinanceController::class, 'verificationDashboard'])->name('verification-dashboard');

            // Finance Audit Trail
            Route::get('/finance-audit-trail',       [\App\Http\Controllers\Admin\FinanceAuditTrailController::class, 'index'])->name('finance-audit-trail.index');
            Route::get('/finance-audit-trail/print', [\App\Http\Controllers\Admin\FinanceAuditTrailController::class, 'print'])->name('finance-audit-trail.print');
            Route::get('/finance-audit-trail/pdf',   [\App\Http\Controllers\Admin\FinanceAuditTrailController::class, 'pdf'])->name('finance-audit-trail.pdf');
            Route::get('/finance-audit-trail/excel', [\App\Http\Controllers\Admin\FinanceAuditTrailController::class, 'excel'])->name('finance-audit-trail.excel');

            // Zoho Comparison
            Route::get('/zoho-comparison',  [\App\Http\Controllers\Admin\FinanceController::class, 'zohoComparison'])->name('zoho-comparison');
            Route::post('/zoho-comparison', [\App\Http\Controllers\Admin\FinanceController::class, 'zohoCompare'])->name('zoho-compare');
        });


        // Sales Department
        Route::prefix('sales-dept')->name('sales-dept.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\SalesDepartmentController::class, 'index'])->name('index');
            Route::get('/reports', [\App\Http\Controllers\Admin\SalesDepartmentController::class, 'reports'])->name('reports');
            Route::get('/reports/print', [\App\Http\Controllers\Admin\SalesDepartmentController::class, 'reportsPrint'])->name('reports.print');
            Route::get('/reports/pdf', [\App\Http\Controllers\Admin\SalesDepartmentController::class, 'reportsPdf'])->name('reports.pdf');
            Route::get('/reports/excel', [\App\Http\Controllers\Admin\SalesDepartmentController::class, 'reportsExcel'])->name('reports.excel');
            Route::get('/targets', [\App\Http\Controllers\Admin\SalesDepartmentController::class, 'targets'])->name('targets');
            Route::get('/targets/print', [\App\Http\Controllers\Admin\SalesDepartmentController::class, 'printTargets'])->name('targets.print');
            Route::get('/targets/pdf', [\App\Http\Controllers\Admin\SalesDepartmentController::class, 'targetsPdf'])->name('targets.pdf');
            Route::get('/targets/excel', [\App\Http\Controllers\Admin\SalesDepartmentController::class, 'targetsExcel'])->name('targets.excel');
            Route::post('/targets', [\App\Http\Controllers\Admin\SalesDepartmentController::class, 'storeTarget'])->name('targets.store');
            Route::put('/targets/{target}', [\App\Http\Controllers\Admin\SalesDepartmentController::class, 'updateTarget'])->name('targets.update');
            Route::delete('/targets/{target}', [\App\Http\Controllers\Admin\SalesDepartmentController::class, 'destroyTarget'])->name('targets.destroy');
        });

        // Transaction Control & Audit
        Route::get('/audit', [\App\Http\Controllers\Admin\AuditController::class, 'index'])->name('audit.index');

        // Auto Follow-up page for sellers
        Route::prefix('auto-followup')->name('auto-followup.')->middleware(['admin.role:saler,admin,super_admin,manager'])->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\AutoFollowupController::class, 'index'])->name('index');
        });

        // Customer management
        Route::prefix('customers')->name('customers.')->group(function () {
            Route::get('/', [CustomerController::class, 'adminIndex'])->name('index');
            Route::get('/map', [CustomerController::class, 'adminMap'])->name('map');
            Route::post('/map/regions', [CustomerController::class, 'storeMapRegion'])->name('map.regions.store');
            Route::post('/map/districts', [CustomerController::class, 'storeMapDistrict'])->name('map.districts.store');
            Route::get('/export/excel', [CustomerController::class, 'exportExcel'])->name('export.excel');
            Route::get('/export/pdf', [CustomerController::class, 'exportPdf'])->name('export.pdf');
            Route::get('/create', [CustomerController::class, 'create'])->name('create');
            Route::get('/duplicates', [CustomerController::class, 'duplicates'])->name('duplicates');
            Route::post('/merge', [CustomerController::class, 'merge'])->name('merge');
            Route::post('/', [CustomerController::class, 'store'])->name('store');
            Route::get('/{customer}', [CustomerController::class, 'adminShow'])->name('show');
            Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->name('edit');
            Route::put('/{customer}', [CustomerController::class, 'update'])->name('update');
            Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('destroy');
            Route::get('/{customer}/transfer-ownership', [CustomerController::class, 'transferOwnershipPage'])->name('transfer-ownership.page');
            Route::post('/{customer}/transfer-ownership', [CustomerController::class, 'transferOwnership'])->name('transfer-ownership');
            Route::post('/{customer}/verify', [CustomerController::class, 'verify'])->name('verify')->withoutMiddleware(['web', 'auth', 'admin']);
            Route::post('/{customer}/unverify', [CustomerController::class, 'unverify'])->name('unverify');
            Route::put('/{customer}/status', [CustomerController::class, 'updateStatus'])->name('status.update');
            
            // Design Gallery
            Route::post('/{customer}/designs', [App\Http\Controllers\Admin\CustomerDesignController::class, 'store'])->name('designs.store');
            Route::delete('/designs/{design}', [App\Http\Controllers\Admin\CustomerDesignController::class, 'destroy'])->name('designs.destroy');

            // Business Profiles
            Route::post('/{customer}/businesses', [CustomerController::class, 'storeBusiness'])->name('businesses.store');
            Route::delete('/businesses/{business}', [CustomerController::class, 'destroyBusiness'])->name('businesses.destroy');
        });

        // Customer Data Center
        Route::prefix('customer-data-center')->name('customer-data-center.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\CustomerDataCenterController::class, 'index'])->name('index');
            Route::get('/{customer}', [App\Http\Controllers\Admin\CustomerDataCenterController::class, 'show'])->name('show');
            Route::post('/{customer}/follow-up', [App\Http\Controllers\Admin\CustomerDataCenterController::class, 'storeFollowUp'])->name('follow-up.store');
            Route::put('/{customer}/follow-up-date', [App\Http\Controllers\Admin\CustomerDataCenterController::class, 'updateFollowUpDate'])->name('follow-up-date.update');
            Route::post('/{customer}/refresh', [App\Http\Controllers\Admin\CustomerDataCenterController::class, 'refreshAnalytics'])->name('refresh');
        });
        // Marketing Module
        Route::prefix('marketing')->name('marketing.')->middleware(['admin.role:super_admin,admin,marketing_manager,manager'])->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Marketing\MarketingDashboardController::class, 'index'])->name('dashboard');
            
            Route::resource('product-penetration', \App\Http\Controllers\Admin\Marketing\ProductPenetrationController::class);
            Route::resource('theme-events', \App\Http\Controllers\Admin\Marketing\ThemeEventController::class);
            Route::resource('campaigns', \App\Http\Controllers\Admin\Marketing\CampaignController::class);
            Route::resource('campaign-activities', \App\Http\Controllers\Admin\Marketing\CampaignActivityController::class);
            Route::resource('calendar', \App\Http\Controllers\Admin\Marketing\MarketingCalendarController::class);
            Route::resource('ads', \App\Http\Controllers\Admin\Marketing\AdsController::class);
            Route::resource('ad-performance', \App\Http\Controllers\Admin\Marketing\AdsPerformanceController::class);
            
            Route::get('reports', [\App\Http\Controllers\Admin\Marketing\MarketingReportController::class, 'index'])->name('reports.index');
            Route::get('reports/print', [\App\Http\Controllers\Admin\Marketing\MarketingReportController::class, 'print'])->name('reports.print');
            Route::get('reports/pdf', [\App\Http\Controllers\Admin\Marketing\MarketingReportController::class, 'pdf'])->name('reports.pdf');
            Route::get('reports/export', [\App\Http\Controllers\Admin\Marketing\MarketingReportController::class, 'export'])->name('reports.export');
        });
    });
});

// Test SMS Routes (for development/testing)
Route::prefix('test-sms')->name('test-sms.')->group(function () {
    Route::get('/', [TestSmsController::class, 'showTestForm'])->name('form');
    Route::post('/send', [TestSmsController::class, 'testSMS'])->name('send');
    Route::get('/send/{phone}', [TestSmsController::class, 'testSMSDefault'])->name('send-default');
    Route::get('/info', [TestSmsController::class, 'getTwilioInfo'])->name('info');
});

// Debug route for testing verify functionality
Route::get('/debug-verify/{customer}', function(\App\Models\Customer $customer) {
    \Log::info('Debug verify route called', ['customer_id' => $customer->id]);
    return response()->json([
        'customer_id' => $customer->id,
        'customer_name' => $customer->name,
        'verified' => $customer->verified,
        'message' => 'Debug route working'
    ]);
})->middleware(['auth', 'admin']);

// Debug POST route for testing verify functionality
Route::post('/debug-verify-post/{customer}', function(\App\Models\Customer $customer) {
    \Log::info('Debug verify POST route called', ['customer_id' => $customer->id]);
    
    try {
        $customer->update(['verified' => true]);
        \Log::info('Customer verification status updated via debug route', ['customer_id' => $customer->id]);
        
        return response()->json([
            'success' => true,
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'verified' => $customer->verified,
            'message' => 'Customer verified successfully via debug route'
        ]);
    } catch (\Exception $e) {
        \Log::error('Debug verify POST failed: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
})->middleware(['auth', 'admin']);

// Simple test route for customer verification
Route::get('/test-verify/{customer}', function(\App\Models\Customer $customer) {
    try {
        $customer->verified = true;
        $customer->is_active = true;
        $saved = $customer->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Customer verified successfully',
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'verified' => $customer->verified,
                'is_active' => $customer->is_active
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to verify customer: ' . $e->getMessage()
        ], 500);
    }
});

// Test POST route for customer verification (without auth and CSRF)
Route::post('/test-verify-post/{id}', function($id) {
    try {
        \Log::info('Test verify POST called', ['customer_id' => $id]);
        
        $customer = \App\Models\Customer::findOrFail($id);
        $customer->verified = true;
        $customer->is_active = true;
        $saved = $customer->save();
        
        \Log::info('Test verify POST success', [
            'customer_id' => $customer->id,
            'saved' => $saved,
            'verified' => $customer->verified
        ]);
        
        return redirect()->back()->with('success', 'Customer verified successfully!');
    } catch (\Exception $e) {
        \Log::error('Test verify POST failed: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Failed to verify customer: ' . $e->getMessage());
    }
})->withoutMiddleware(['web']);

// API Routes for cart order creation
Route::prefix('api')->group(function () {
    Route::post('/orders/create-from-cart', [OrderController::class, 'createFromCart'])
        ->middleware('auth:customer')
        ->name('api.orders.create-from-cart');
    Route::post('/orders/create-from-cart-data', [OrderController::class, 'createFromCartData'])
        ->name('api.orders.create-from-cart-data');
});

// Debug route for form submission
Route::post('/debug-form', function (\Illuminate\Http\Request $request) {
    \Log::info('DEBUG FORM SUBMISSION', [
        'method' => $request->method(),
        'url' => $request->url(),
        'data' => $request->all(),
        'headers' => $request->headers->all()
    ]);
    return response()->json(['status' => 'success', 'message' => 'Form received']);
});

// Test form route
Route::get('/test-form', function () {
    return view('test-form');
});

// Test checkout with cart
Route::get('/test-checkout-debug', function() {
    $cart = [
        [
            'id' => 'test-item-1',
            'product_id' => 1,
            'name' => 'Test Product',
            'price' => 10000,
            'quantity' => 1,
            'image' => null,
            'variations' => [],
            'addons' => [],
            'custom_inputs' => []
        ]
    ];
    
    session(['cart' => $cart]);
    
    $includeVAT = false;
    $totals = [
        'subtotal' => 10000,
        'vat' => 0,
        'shipping' => 0,
        'total' => 10000,
        'item_count' => 1
    ];
    
    \Log::info('Test checkout debug - cart set', [
        'cart' => $cart,
        'session_id' => session()->getId(),
        'session_cart' => session('cart', [])
    ]);
    
    return view('public.checkout', compact('cart', 'totals'));
});

// Test real user flow
Route::get('/test-real-flow', function() {
    \Log::info('Real flow test page accessed', [
        'session_id' => session()->getId()
    ]);
    
    return view('test-real-flow');
});

// Test CSRF checkout form
Route::get('/test-csrf-checkout', function() {
    // Set up cart for the test
    $cart = [
        [
            'id' => 'csrf-test-item-1',
            'product_id' => 1,
            'name' => 'CSRF Test Product',
            'price' => 20000,
            'quantity' => 1,
            'image' => null,
            'variations' => [],
            'addons' => [],
            'custom_inputs' => []
        ]
    ];
    
    session(['cart' => $cart]);
    
    \Log::info('CSRF checkout test - cart set', [
        'cart' => $cart,
        'session_id' => session()->getId()
    ]);
    
    return view('test-csrf-checkout');
});

// Test browser checkout form
Route::get('/test-browser-checkout', function() {
    // Set up cart for the test
    $cart = [
        [
            'id' => 'browser-test-item-1',
            'product_id' => 1,
            'name' => 'Browser Test Product',
            'price' => 15000,
            'quantity' => 2,
            'image' => null,
            'variations' => [],
            'addons' => [],
            'custom_inputs' => []
        ]
    ];
    
    session(['cart' => $cart]);
    
    \Log::info('Browser checkout test - cart set', [
        'cart' => $cart,
        'session_id' => session()->getId()
    ]);
    
    return view('test-browser-checkout');
});

// Test simple checkout form
Route::get('/test-simple-checkout', function() {
    // Set up cart for the test
    $cart = [
        [
            'id' => 'test-item-1',
            'product_id' => 1,
            'name' => 'Test Product',
            'price' => 10000,
            'quantity' => 1,
            'image' => null,
            'variations' => [],
            'addons' => [],
            'custom_inputs' => []
        ]
    ];
    
    session(['cart' => $cart]);
    
    \Log::info('Simple checkout test - cart set', [
        'cart' => $cart,
        'session_id' => session()->getId()
    ]);
    
    return view('test-simple-checkout');
});

// Test guest order creation (bypass CSRF)
Route::post('/test-guest-order-bypass', function() {
    \Log::info('=== TESTING GUEST ORDER BYPASS ===');
    
    // Create guest user
    $user = \App\Models\User::firstOrCreate(
        ['email' => 'guest@chibobrand.com'],
        [
            'name' => 'Guest Customer',
            'phone' => 'WhatsApp Order',
            'email' => 'guest@chibobrand.com',
            'password' => bcrypt('guest'),
            'email_verified_at' => now(),
            'is_verified' => true,
            'user_type' => 'customer'
        ]
    );
    
    // Create order
    $order = \App\Models\Order::create([
        'user_id' => $user->id,
        'order_code' => 'CHB-' . date('Ymd') . '-BYPASS-' . rand(1000, 9999),
        'total_amount' => 25000,
        'payment_status' => 'pending',
        'approval_status' => 'requested',
        'notes' => 'Bypass test order creation',
    ]);
    
    // Create order item
    \App\Models\OrderItem::create([
        'order_id' => $order->id,
        'product_id' => 1,
        'quantity' => 2,
        'unit_price' => 12500,
        'subtotal' => 25000,
        'product_variations' => [],
        'product_addons' => [],
        'custom_inputs' => [],
    ]);
    
    \Log::info('Bypass order created', [
        'order_id' => $order->id,
        'order_code' => $order->order_code,
        'user_id' => $user->id,
        'is_guest' => true
    ]);
    
    return response()->json([
        'success' => true,
        'order_id' => $order->id,
        'order_code' => $order->order_code,
        'message' => 'Bypass order created successfully!'
    ]);
})->withoutMiddleware(['web']);

// Test direct order creation (bypass form)
Route::get('/test-direct-order', function() {
    \Log::info('=== TESTING DIRECT ORDER CREATION ===');
    
    // Create guest user
    $user = \App\Models\User::firstOrCreate(
        ['email' => 'guest@chibobrand.com'],
        [
            'name' => 'Guest Customer',
            'phone' => 'WhatsApp Order',
            'email' => 'guest@chibobrand.com',
            'password' => bcrypt('guest'),
            'email_verified_at' => now(),
            'is_verified' => true,
            'user_type' => 'customer'
        ]
    );
    
    // Create order
    $order = \App\Models\Order::create([
        'user_id' => $user->id,
        'order_code' => 'CHB-' . date('Ymd') . '-DIRECT-' . rand(1000, 9999),
        'total_amount' => 15000,
        'payment_status' => 'pending',
        'approval_status' => 'requested',
        'notes' => 'Direct test order creation',
    ]);
    
    // Create order item
    \App\Models\OrderItem::create([
        'order_id' => $order->id,
        'product_id' => 1,
        'quantity' => 1,
        'unit_price' => 15000,
        'subtotal' => 15000,
        'product_variations' => [],
        'product_addons' => [],
        'custom_inputs' => [],
    ]);
    
    \Log::info('Direct order created', [
        'order_id' => $order->id,
        'order_code' => $order->order_code,
        'user_id' => $user->id,
        'is_guest' => true
    ]);
    
    return response()->json([
        'success' => true,
        'order_id' => $order->id,
        'order_code' => $order->order_code,
        'message' => 'Direct order created successfully!'
    ]);
});

// ============================================================================
// GATEKEEPER SYSTEM ROUTES
// ============================================================================

Route::prefix('gatekeeper')->name('gatekeeper.')->middleware(['auth', 'admin.role:gatekeeper,delivery,admin,super_admin,accountant'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Gatekeeper\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/movements', [App\Http\Controllers\Gatekeeper\ProductMovementController::class, 'index'])->name('movements.index');
    Route::get('/movements/print-filtered', [App\Http\Controllers\Gatekeeper\ProductMovementController::class, 'printFiltered'])->name('movements.print-filtered');
    Route::get('/movements/pdf', [App\Http\Controllers\Gatekeeper\ProductMovementController::class, 'exportPdf'])->name('movements.pdf');
    Route::get('/movements/excel', [App\Http\Controllers\Gatekeeper\ProductMovementController::class, 'exportExcel'])->name('movements.excel');
    Route::get('/movements/create', [App\Http\Controllers\Gatekeeper\ProductMovementController::class, 'create'])->name('movements.create');
    Route::get('/customers/search', [App\Http\Controllers\Gatekeeper\ProductMovementController::class, 'searchCustomers'])->name('customers.search');
    Route::get('/tasks/search', [App\Http\Controllers\Gatekeeper\ProductMovementController::class, 'searchTasks'])->name('tasks.search');
    Route::post('/movements', [App\Http\Controllers\Gatekeeper\ProductMovementController::class, 'store'])->name('movements.store');
    Route::get('/movements/{movement}', [App\Http\Controllers\Gatekeeper\ProductMovementController::class, 'show'])->name('movements.show');
    Route::get('/deliver', [App\Http\Controllers\Gatekeeper\ProductMovementController::class, 'deliverIndex'])->name('deliver');
    Route::post('/tasks/{task}/mark-delivered', [App\Http\Controllers\Gatekeeper\ProductMovementController::class, 'markDelivered'])->name('tasks.mark-delivered');
});


