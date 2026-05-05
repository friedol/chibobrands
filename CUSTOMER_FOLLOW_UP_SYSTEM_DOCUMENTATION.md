# Customer Follow-up System - Complete Documentation

## Table of Contents
1. [Overview](#overview)
2. [System Architecture](#system-architecture)
3. [Database Schema](#database-schema)
4. [Models & Relationships](#models--relationships)
5. [Controllers & Routes](#controllers--routes)
6. [Services](#services)
7. [Admin Dashboard (Data Center)](#admin-dashboard-data-center)
8. [Workflow & Process Flow](#workflow--process-flow)
9. [Console Commands](#console-commands)
10. [Notifications](#notifications)
11. [Usage Guide for Sellers](#usage-guide-for-sellers)
12. [API Reference](#api-reference)
13. [Best Practices](#best-practices)

---

## Overview

The **Customer Follow-up System** is an intelligent, predictive sales management platform designed to help sellers maintain consistent engagement with customers based on their purchase patterns and behavior. It uses historical order data to predict when customers are likely to place their next order, enabling proactive follow-up at the optimal time.

### Key Features

- **Predictive Analytics**: Automatically calculates average reorder intervals based on customer purchase history
- **Smart Status Tracking**: Categorizes customers into Overdue, Due Today, Upcoming, or New Customer
- **Priority Ranking**: Ranks high-value customers to focus on the most important prospects
- **Manual Control**: Sellers can override automated predictions with manual follow-up dates
- **Activity Logging**: Complete history of all follow-up activities per customer
- **Multi-Channel Support**: Tracks phone calls, WhatsApp, email, and in-person meetings
- **Automated Reminders**: Notifies sellers about customers needing follow-ups
- **Analytics Dashboard**: Real-time insights into customer purchase patterns and task type preferences

---

## System Architecture

### Component Hierarchy

```
┌─────────────────────────────────────────────────────────────────────┐
│                        Customer Follow-up System                    │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  ┌────────────────────────────────────────────────────────────┐   │
│  │              Customer Data Center (UI Layer)               │   │
│  │  - List View (filtered by status/priority)                │   │
│  │  - Detail View (analytics & history)                      │   │
│  │  - Modal Interactions (contact, schedule)                 │   │
│  └────────────────┬─────────────────────────────────────────┘   │
│                   │                                              │
│  ┌────────────────▼──────────────────────────────────────────┐   │
│  │        Controller Layer                                    │   │
│  │  CustomerDataCenterController                             │   │
│  │  - index()              - List all customers              │   │
│  │  - show()               - Customer details & analytics    │   │
│  │  - storeFollowUp()      - Record follow-up activity       │   │
│  │  - updateFollowUpDate() - Manual date scheduling          │   │
│  │  - refreshAnalytics()   - Recalculate stats              │   │
│  └────────────────┬──────────────────────────────────────────┘   │
│                   │                                              │
│  ┌────────────────▼──────────────────────────────────────────┐   │
│  │        Service Layer                                       │   │
│  │  CustomerAnalyticsService                                 │   │
│  │  - recalculateCustomerAnalytics()                         │   │
│  │  - determineFollowUpStatus()                             │   │
│  │  - calculatePriority()                                    │   │
│  │  - updateTaskTypeAnalytics()                             │   │
│  │  - determineStatus()                                      │   │
│  └────────────────┬──────────────────────────────────────────┘   │
│                   │                                              │
│  ┌────────────────▼──────────────────────────────────────────┐   │
│  │        Model Layer                                         │   │
│  │  ┌─────────────────────────────────────────────────────┐  │   │
│  │  │ Customer (Parent)                                   │  │   │
│  │  │ - total_orders, total_spent                         │  │   │
│  │  │ - avg_reorder_interval                              │  │   │
│  │  │ - next_expected_order_date                          │  │   │
│  │  │ - manual_follow_up_date                             │  │   │
│  │  │ - follow_up_status                                  │  │   │
│  │  │ - priority_ranking                                  │  │   │
│  │  └─────────────────────────────────────────────────────┘  │   │
│  │                       │                                    │   │
│  │  ┌────────────────────▼────────────────────────────────┐  │   │
│  │  │ CustomerFollowUp (Child)                            │  │   │
│  │  │ - customer_id, user_id                              │  │   │
│  │  │ - follow_up_date, action, notes                     │  │   │
│  │  └─────────────────────────────────────────────────────┘  │   │
│  │                                                          │   │
│  │  ┌────────────────────────────────────────────────────┐  │   │
│  │  │ CustomerProductAnalytic (Child)                    │  │   │
│  │  │ - Tracks per-product purchase patterns             │  │   │
│  │  └─────────────────────────────────────────────────────┘  │   │
│  │                                                          │   │
│  │  ┌────────────────────────────────────────────────────┐  │   │
│  │  │ CustomerTaskTypeAnalytic (Child)                   │  │   │
│  │  │ - Tracks per-task-type purchase patterns           │  │   │
│  │  └─────────────────────────────────────────────────────┘  │   │
│  └──────────────────────────────────────────────────────────┘   │
│                                                                     │
│  ┌────────────────────────────────────────────────────────────┐   │
│  │        Background Processes                                │   │
│  │  Console Commands:                                         │   │
│  │  - customers:update-follow-ups (Scheduled daily)           │   │
│  │                                                            │   │
│  │  Notifications:                                            │   │
│  │  - CustomerFollowUpReminder (Database notification)        │   │
│  └────────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────────┘
```

### Data Flow

```
1. Customer Places Order
   ↓
2. DesignTask Created & Completed
   ↓
3. Scheduler Runs (Daily): customers:update-follow-ups
   ↓
4. CustomerAnalyticsService::recalculateCustomerAnalytics($customerId)
   ├─ Fetch all completed design tasks
   ├─ Calculate intervals between orders
   ├─ Compute average reorder interval
   ├─ Predict next expected order date
   ├─ Determine follow-up status
   ├─ Calculate priority ranking
   └─ Update customer record + task type analytics
   ↓
5. Schedulerupdate statuses in database
   ├─ Compare old vs new status
   ├─ Send notifications for Due/Overdue customers
   └─ Notify assigned seller & admins
   ↓
6. Seller Views Customer Data Center Dashboard
   ├─ Filtered by status (Due Today, Overdue, Upcoming, New)
   ├─ Sorted by priority ranking
   └─ Ready for outreach
   ↓
7. Seller Records Follow-up Activity
   ├─ Select channel (WhatsApp, Phone, Email, In-Person)
   ├─ Add conversation notes
   ├─ Optionally override next follow-up date
   └─ Activity logged to CustomerFollowUp table
   ↓
8. System Recalculates Analytics
   └─ Updates next expected date if manually overridden
```

---

## Database Schema

### 1. Customers Table (Extended Fields)

The base `customers` table has been extended with follow-up analytics fields:

```sql
ALTER TABLE customers ADD COLUMN (
    total_orders INT DEFAULT 0,
    total_spent DECIMAL(15, 2) DEFAULT 0,
    avg_reorder_interval INT NULLABLE,              -- Days between reorders
    last_order_date DATE NULLABLE,                  -- Last completed task date
    next_expected_order_date DATE NULLABLE,         -- Predicted next order date
    manual_follow_up_date DATE NULLABLE,            -- Manual override (if set)
    follow_up_status VARCHAR(255) DEFAULT 'New Customer',
    priority_ranking INT DEFAULT 0                  -- Higher = more valuable
);

CREATE INDEX idx_next_expected_order_date ON customers(next_expected_order_date);
CREATE INDEX idx_follow_up_status ON customers(follow_up_status);
CREATE INDEX idx_priority_ranking ON customers(priority_ranking);
```

**Field Descriptions:**

| Field | Type | Description | Example |
|-------|------|-------------|---------|
| `total_orders` | INT | Total completed design tasks | 12 |
| `total_spent` | DECIMAL | Total revenue from customer | 45,000.00 |
| `avg_reorder_interval` | INT | Average days between orders | 30 |
| `last_order_date` | DATE | Most recent task completion | 2026-03-15 |
| `next_expected_order_date` | DATE | Predicted next order date | 2026-04-14 |
| `manual_follow_up_date` | DATE | Seller-set override date | NULL or 2026-04-20 |
| `follow_up_status` | VARCHAR | Current status classification | "Due Today", "Overdue", "Upcoming", "New Customer" |
| `priority_ranking` | INT | Revenue-based priority score | 150 |

### 2. CustomerFollowUp Table (Activity Log)

Stores individual follow-up interactions:

```sql
CREATE TABLE customer_follow_ups (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    customer_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,           -- Seller who performed follow-up
    follow_up_date DATE NOT NULL,               -- When the follow-up occurred
    action VARCHAR(255) NULLABLE,               -- Channel used (WhatsApp, Phone, etc.)
    notes TEXT NULLABLE,                        -- Conversation summary
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_customer_follow_up_date (customer_id, follow_up_date)
);
```

**Fields:**

| Field | Type | Description | Example |
|-------|------|-------------|---------|
| `customer_id` | BIGINT | Reference to customer | 45 |
| `user_id` | BIGINT | Seller who recorded activity | 8 |
| `follow_up_date` | DATE | When contact was made | 2026-04-02 |
| `action` | VARCHAR | Communication channel | "WhatsApp", "Phone Call", "Email" |
| `notes` | TEXT | Outcome/summary | "Customer interested in bulk order" |

### 3. CustomerProductAnalytic Table

Per-product purchase patterns:

```sql
CREATE TABLE customer_product_analytics (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    customer_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    avg_reorder_interval INT NULLABLE,              -- Days between purchases
    last_purchase_date DATE NULLABLE,
    next_expected_purchase_date DATE NULLABLE,
    total_quantity_bought INT DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_customer_product (customer_id, product_id)
);
```

### 4. CustomerTaskTypeAnalytic Table

Per-task-type purchase patterns:

```sql
CREATE TABLE customer_task_type_analytics (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    customer_id BIGINT UNSIGNED NOT NULL,
    design_task_type_id BIGINT UNSIGNED NOT NULL,
    avg_reorder_interval INT NULLABLE,              -- Days between purchases
    last_purchase_date DATE NULLABLE,
    next_expected_purchase_date DATE NULLABLE,
    total_quantity_bought INT DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (design_task_type_id) REFERENCES design_task_types(id) ON DELETE CASCADE,
    UNIQUE KEY unique_customer_tasktype (customer_id, design_task_type_id)
);
```

---

## Models & Relationships

### Customer Model

**File:** `app/Models/Customer.php`

```php
class Customer extends Authenticatable
{
    // Relationships
    public function followUps(): HasMany
    {
        return $this->hasMany(CustomerFollowUp::class);
    }

    public function productAnalytics(): HasMany
    {
        return $this->hasMany(CustomerProductAnalytic::class);
    }

    public function taskTypeAnalytics(): HasMany
    {
        return $this->hasMany(CustomerTaskTypeAnalytic::class);
    }

    public function designTasks(): HasMany
    {
        return $this->hasMany(DesignTask::class);
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    // Accessors
    public function getStatusColorAttribute()
    {
        return match($this->follow_up_status) {
            'Overdue' => 'danger',
            'Due Today' => 'warning',
            'Upcoming' => 'primary',
            'Active' => 'info',
            'New Customer' => 'success',
            default => 'secondary'
        };
    }

    public function getEffectiveFollowUpDateAttribute()
    {
        // Manual override takes precedence
        return $this->manual_follow_up_date ?: $this->next_expected_order_date;
    }

    // Scopes
    public function scopeForSaler($query, $user)
    {
        if (!$user || $user->role !== 'saler') {
            return $query;
        }
        return $query->where('added_by', $user->id);
    }

    public function scopeVerified($query)
    {
        return $query->where('verified', true);
    }

    public function scopeWholesale($query)
    {
        return $query->where('is_wholesale', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
```

### CustomerFollowUp Model

**File:** `app/Models/CustomerFollowUp.php`

```php
class CustomerFollowUp extends Model
{
    protected $fillable = [
        'customer_id',
        'user_id',
        'follow_up_date',
        'action',
        'notes',
    ];

    protected $casts = [
        'follow_up_date' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

### Relationships Diagram

```
Customer (1) ──→ (Many) CustomerFollowUp
    │
    ├──→ (Many) CustomerProductAnalytic
    │
    ├──→ (Many) CustomerTaskTypeAnalytic
    │
    ├──→ (Many) DesignTask
    │
    ├──→ (Many) CustomerDesign
    │
    └──→ (1) User (added_by)

CustomerFollowUp (Many) ──→ (1) Customer
                          │
                          └──→ (1) User (seller)
```

---

## Controllers & Routes

### CustomerDataCenterController

**File:** `app/Http/Controllers/Admin/CustomerDataCenterController.php`

#### Route Registration

```php
// routes/web.php
Route::middleware(['auth', 'admin'])->group(function () {
    Route::prefix('customer-data-center')->name('customer-data-center.')->group(function () {
        Route::get('/', [CustomerDataCenterController::class, 'index'])->name('index');
        Route::get('/{customer}', [CustomerDataCenterController::class, 'show'])->name('show');
        Route::post('/{customer}/follow-up', [CustomerDataCenterController::class, 'storeFollowUp'])->name('follow-up.store');
        Route::put('/{customer}/follow-up-date', [CustomerDataCenterController::class, 'updateFollowUpDate'])->name('follow-up-date.update');
        Route::post('/{customer}/refresh', [CustomerDataCenterController::class, 'refreshAnalytics'])->name('refresh');
    });
});
```

#### Method: index()

**Purpose:** Display paginated customer list filtered by follow-up status

**Route:** `GET /customer-data-center`

**Query Parameters:**
- `status` (optional): `due`, `upcoming`, `new` (default: `due`)
- `page` (optional): Pagination number

**Response:**
- View: `admin.customers.data-center.index`
- Variables: `$customers`, `$stats`, `$statusFilter`

**Logic:**
```php
public function index(Request $request)
{
    $user = Auth::user();
    $query = Customer::query()->forSaler($user);

    $statusFilter = $request->get('status', 'due');
    
    if ($statusFilter === 'due') {
        $query->whereIn('follow_up_status', ['Due Today', 'Overdue']);
    } elseif ($statusFilter === 'upcoming') {
        $query->where('follow_up_status', 'Upcoming');
    } elseif ($statusFilter === 'new') {
        $query->where('follow_up_status', 'New Customer');
    }

    $customers = $query->orderBy('priority_ranking', 'desc')
        ->orderBy('next_expected_order_date', 'asc')
        ->paginate(15)
        ->withQueryString();

    // Calculate summary statistics
    $stats = [
        'due_today' => Customer::forSaler($user)->where('follow_up_status', 'Due Today')->count(),
        'overdue' => Customer::forSaler($user)->where('follow_up_status', 'Overdue')->count(),
        'upcoming' => Customer::forSaler($user)->where('follow_up_status', 'Upcoming')->count(),
        'total_customers' => Customer::forSaler($user)->count(),
    ];

    return view('admin.customers.data-center.index', compact('customers', 'stats', 'statusFilter'));
}
```

#### Method: show()

**Purpose:** Display detailed customer analytics and activity history

**Route:** `GET /customer-data-center/{customer}`

**Response:**
- View: `admin.customers.data-center.show`
- Variables: `$customer` (with loaded relationships)

**Logic:**
```php
public function show(Customer $customer)
{
    // Load all necessary relationships
    $customer->load([
        'followUps.user',
        'designs',
        'taskTypeAnalytics.designTaskType'
    ]);
    
    $customer->setRelation('designTasks', $customer->designTasks()
        ->latest()
        ->take(10)
        ->get());
    
    return view('admin.customers.data-center.show', compact('customer'));
}
```

#### Method: storeFollowUp()

**Purpose:** Record a follow-up activity for a customer

**Route:** `POST /customer-data-center/{customer}/follow-up`

**Request Validation:**
```
- action (required, string)
- notes (optional, string)
- next_follow_up_date (optional, date, after_or_equal: today)
```

**Request Body Example:**
```json
{
    "action": "WhatsApp",
    "notes": "Customer interested in bulk design order",
    "next_follow_up_date": "2026-04-15"
}
```

**Logic:**
```php
public function storeFollowUp(Request $request, Customer $customer)
{
    $request->validate([
        'action' => 'required|string',
        'notes' => 'nullable|string',
        'next_follow_up_date' => 'nullable|date|after_or_equal:today',
    ]);

    // Create follow-up record
    CustomerFollowUp::create([
        'customer_id' => $customer->id,
        'user_id' => Auth::id(),
        'action' => $request->action,
        'notes' => $request->notes,
        'follow_up_date' => now(),
    ]);

    // Override automatic prediction if manual date provided
    if ($request->next_follow_up_date) {
        $customer->update([
            'manual_follow_up_date' => $request->next_follow_up_date,
        ]);
    }

    // Recalculate status and analytics
    $this->analyticsService->recalculateCustomerAnalytics($customer->id);

    return redirect()->back()->with('success', 'Follow-up activity recorded successfully.');
}
```

#### Method: updateFollowUpDate()

**Purpose:** Manually schedule the next follow-up date

**Route:** `PUT /customer-data-center/{customer}/follow-up-date`

**Request Validation:**
```
- manual_follow_up_date (required, date)
```

**Request Body Example:**
```json
{
    "manual_follow_up_date": "2026-04-20"
}
```

**Logic:**
```php
public function updateFollowUpDate(Request $request, Customer $customer)
{
    $request->validate([
        'manual_follow_up_date' => 'required|date',
    ]);

    $customer->update([
        'manual_follow_up_date' => $request->manual_follow_up_date,
    ]);

    // Recalculate status
    $this->analyticsService->recalculateCustomerAnalytics($customer->id);

    return redirect()->back()->with('success', 'Next follow-up date has been manually scheduled.');
}
```

#### Method: refreshAnalytics()

**Purpose:** Manually trigger analytics recalculation

**Route:** `POST /customer-data-center/{customer}/refresh`

**Logic:**
```php
public function refreshAnalytics(Customer $customer)
{
    $this->analyticsService->recalculateCustomerAnalytics($customer->id);
    return redirect()->back()->with('success', 'Analytics refreshed successfully.');
}
```

---

## Services

### CustomerAnalyticsService

**File:** `app/Services/CustomerAnalyticsService.php`

This is the core service that powers all follow-up predictions and analytics.

#### Method: recalculateCustomerAnalytics()

**Purpose:** Recalculate all analytics for a customer

**Parameters:**
- `int $customerId` - Customer ID to recalculate

**Logic:**
1. Fetch all completed design tasks for the customer
2. Sort tasks chronologically
3. Calculate order intervals between consecutive tasks
4. Compute average reorder interval
5. Predict next expected order date
6. Determine follow-up status
7. Calculate priority ranking
8. Update customer record
9. Update task-type analytics

```php
public function recalculateCustomerAnalytics(int $customerId)
{
    $customer = Customer::find($customerId);
    if (!$customer) return;

    // Fetch completed design tasks (only successful orders)
    $designTasks = \App\Models\DesignTask::where('customer_id', $customerId)
        ->whereIn('status', [
            'completed',
            'confirmed',
            'printed',
            'super_completed'
        ])
        ->orderBy('created_at', 'asc')
        ->get();

    if ($designTasks->isEmpty()) {
        // No order history yet
        $customer->update([
            'total_orders' => 0,
            'total_spent' => 0,
            'avg_reorder_interval' => null,
            'last_order_date' => null,
            'next_expected_order_date' => null,
            'follow_up_status' => 'New Customer',
        ]);
        return;
    }

    // Calculate totals
    $totalOrders = $designTasks->count();
    $totalSpent = $designTasks->sum(function ($task) {
        return $task->requires_receipt ? $task->price * 1.18 : $task->price;
    });
    $lastOrderDate = $designTasks->last()->created_at;

    // Calculate intervals between orders
    $intervals = [];
    for ($i = 1; $i < $totalOrders; $i++) {
        $prevDate = Carbon::parse($designTasks[$i - 1]->created_at);
        $currDate = Carbon::parse($designTasks[$i]->created_at);
        $intervals[] = $prevDate->diffInDays($currDate);
    }

    $avgInterval = count($intervals) > 0 
        ? (int)(array_sum($intervals) / count($intervals)) 
        : null;

    // Predict next order date
    $nextExpectedDate = $avgInterval 
        ? Carbon::parse($lastOrderDate)->addDays($avgInterval) 
        : null;

    // Determine status and priority
    $status = $this->determineStatus($nextExpectedDate);
    $priority = $this->calculatePriority($totalSpent, $totalOrders);

    // Update customer
    $customer->update([
        'total_orders' => $totalOrders,
        'total_spent' => $totalSpent,
        'avg_reorder_interval' => $avgInterval,
        'last_order_date' => $lastOrderDate->toDateString(),
        'next_expected_order_date' => $nextExpectedDate,
        'follow_up_status' => $status,
        'priority_ranking' => $priority,
    ]);

    // Update task-type analytics
    $this->updateTaskTypeAnalytics($customerId, $designTasks);
}
```

#### Method: determineStatus()

**Purpose:** Calculate follow-up status based on next expected date

**Parameters:**
- `?Carbon $nextExpectedDate` - Predicted next order date

**Returns:** `string` - Status code

**Status Rules:**
```
- No date: "New Customer"
- Today: "Due Today"
- Past date: "Overdue"
- Within 3 days: "Upcoming"
- More than 3 days: "Upcoming"
```

```php
public function determineStatus(?Carbon $nextExpectedDate): string
{
    if (!$nextExpectedDate) return 'New Customer';

    $today = Carbon::today();
    $expected = Carbon::parse($nextExpectedDate)->startOfDay();

    if ($expected->isSameDay($today)) {
        return 'Due Today';
    }
    
    if ($expected->isPast()) {
        return 'Overdue';
    }
    
    if ($expected->diffInDays($today) <= 3) {
        return 'Upcoming';
    }
    
    return 'Upcoming';
}
```

#### Method: calculatePriority()

**Purpose:** Calculate customer priority ranking

**Parameters:**
- `float $totalSpent` - Total revenue from customer
- `int $totalOrders` - Total order count

**Returns:** `int` - Priority ranking (higher = more valuable)

**Formula:** Base score (0) + spent factor (0.5 per 1,000) + order factor (5 per order)

```php
public function calculatePriority(float $totalSpent, int $totalOrders): int
{
    $spentFactor = floor($totalSpent / 1000) * 0.5;
    $orderFactor = $totalOrders * 5;
    return (int)($spentFactor + $orderFactor);
}
```

#### Method: updateTaskTypeAnalytics()

**Purpose:** Update per-task-type purchase patterns

```php
protected function updateTaskTypeAnalytics(int $customerId, $designTasks)
{
    $taskTypePurchases = [];

    // Group purchases by task type
    foreach ($designTasks as $task) {
        if (!$task->design_task_type_id) continue;
        
        if (!isset($taskTypePurchases[$task->design_task_type_id])) {
            $taskTypePurchases[$task->design_task_type_id] = [];
        }
        
        $taskTypePurchases[$task->design_task_type_id][] = [
            'date' => $task->created_at,
            'quantity' => $task->qty
        ];
    }

    // Calculate per-type analytics
    foreach ($taskTypePurchases as $taskTypeId => $purchases) {
        $totalQty = array_sum(array_column($purchases, 'quantity'));
        $lastPurchaseDate = end($purchases)['date'];
        
        // Calculate intervals
        $intervals = [];
        for ($i = 1; $i < count($purchases); $i++) {
            $prevDate = Carbon::parse($purchases[$i - 1]['date']);
            $currDate = Carbon::parse($purchases[$i]['date']);
            $intervals[] = $prevDate->diffInDays($currDate);
        }

        $avgInterval = count($intervals) > 0 
            ? (int)(array_sum($intervals) / count($intervals)) 
            : null;
        $nextExpectedDate = $avgInterval 
            ? Carbon::parse($lastPurchaseDate)->addDays($avgInterval) 
            : null;

        // Upsert analytics record
        \App\Models\CustomerTaskTypeAnalytic::updateOrCreate(
            ['customer_id' => $customerId, 'design_task_type_id' => $taskTypeId],
            [
                'avg_reorder_interval' => $avgInterval,
                'last_purchase_date' => $lastPurchaseDate,
                'next_expected_purchase_date' => $nextExpectedDate,
                'total_quantity_bought' => $totalQty,
            ]
        );
    }
}
```

---

## Admin Dashboard (Data Center)

### List View: `/customer-data-center`

**File:** `resources/views/admin/customers/data-center/index.blade.php`

#### UI Components

1. **Header Section**
   - Title & Description
   - Status Filter Tabs (Due Today/Overdue, Upcoming, New Customers)
   - Refresh Button

2. **Statistics Cards**
   - Overdue Count (Red, Priority Alert)
   - Due Today Count (Orange, Action Required)
   - Upcoming Count (Blue, Pipeline)
   - Total Analyzed (Green, System-wide)

3. **Main Follow-up Table**
   - **Columns:**
     - Customer Details (Name, Phone, Company)
     - Follow-up Status (Badge with color)
     - Purchase Cycle (Average days)
     - Next Expected Date (With "days until" indicator)
     - Priority Ranking (Visual progress bar)
     - Actions (View Details, Contact)

4. **Search Filter**
   - Real-time client-side search across customer names, phone, company

#### Table Row Features

```html
<!-- Customer Details Column -->
<div class="customer-card">
    <img src="avatar" class="rounded-circle">
    <div>
        <h6>Customer Name</h6>
        <span>📱 +1-234-567-8900</span>
        <span class="badge">Company Name (if exists)</span>
    </div>
</div>

<!-- Status Column -->
<span class="badge bg-{status_color}">{{ follow_up_status }}</span>

<!-- Priority Column -->
<div class="progress">
    <div style="width: {{ priority/3 }}%"></div>
</div>
<span>#{{ priority }}</span>

<!-- Actions Dropdown -->
<button class="btn-actions">ACTIONS</button>
- View Insights
- Direct Contact (WhatsApp)
- Full Profile
```

### Detail View: `/customer-data-center/{customer}`

**File:** `resources/views/admin/customers/data-center/show.blade.php`

#### Left Column: Analytics Cards

1. **Customer Profile Card**
   - Name, Phone, Email, Company
   - Customer Status (Active/Inactive)
   - Added by (Seller name)
   - Wholesale Status

2. **Metrics Cards**
   - Total Orders: Number of completed tasks
   - Total Spent: Total revenue generated
   - Avg Reorder Cycle: Days between orders
   - Last Order: Date of most recent task

3. **Activity History Timeline**
   - Date-ordered list of follow-up activities
   - Each activity shows:
     - Action type (WhatsApp, Phone, etc.)
     - Notes from seller
     - Seller who recorded it
     - Timestamp

#### Right Column: Smart Prediction & Actions

1. **Smart Prediction Card**
   - Large status badge (Color-coded)
   - Next Expected Order Date (Large, color-coded)
   - "Days until/since" indicator
   - Info box with insights:
     - "This customer reorders every X days"
     - "Current purchasing power ranked at #X"

2. **Action Buttons**
   - "Contact Customer" (WhatsApp direct link)
   - "Set Manual Date" (Override prediction)

#### Modals

**Contact Customer Modal**
```
- Action Channel Select (WhatsApp/Phone/Email/In-Person)
- Conversation Notes Textarea
- Manual Follow-up Date Override
- WhatsApp Direct Link Button
- Save Log Button
```

**Schedule Manual Date Modal**
```
- Date Input Field
- Explanation text
- Update/Cancel Buttons
```

---

## Workflow & Process Flow

### 1. Customer Creation
- New customer added to system (by seller/admin)
- Status: "New Customer"
- Fields set to defaults (0 orders, no dates)

### 2. Initial Order
- Customer completes first design task
- Task marked as "completed"

### 3. Daily Scheduler Run
- **Command:** `php artisan customers:update-follow-ups`
- **When:** Daily at scheduled time (configurable)
- **Per Customer:**
  - Fetch all completed tasks
  - Recalculate analytics
  - Update follow-up status
  - Check if seller should be notified

### 4. Seller Views Dashboard
- Navigate to `/customer-data-center`
- Filter by status (default: "Due Today" & "Overdue")
- Lists customers in priority order
- See actionable insights

### 5. Seller Contacts Customer
- Click "Direct Contact" button
- Opened pre-filled WhatsApp link
- Also can select channel (Phone, Email, etc.)
- Enter conversation notes
- Optionally override next follow-up date
- Submit

### 6. Log Recording
- Follow-up activity created in database
- Analytics may be recalculated if date overridden
- New follow-up status computed

### 7. Repeat Cycle
- Next day, scheduler runs again
- Customers moving into "Due Today" get notifications
- Overdue customers get high-priority alerts
- Cycle continues

---

## Console Commands

### command: customers:update-follow-ups

**File:** `app/Console/Commands/UpdateCustomerFollowUpStatuses.php`

**Usage:**
```bash
php artisan customers:update-follow-ups
```

**Scheduling:**
```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->command('customers:update-follow-ups')->daily(); // Runs every day
    // Or more specifically:
    $schedule->command('customers:update-follow-ups')->dailyAt('06:00'); // 6 AM daily
}
```

**Execution Flow:**
```
1. Fetch all customers with next_expected_order_date or manual_follow_up_date
2. For each customer:
   a. Store old follow_up_status
   b. Call analyticsService->determineFollowUpStatus()
   c. Compare old vs new status
   d. If status changed:
      - Update customer record
      - Increment change counter
   e. If status is "Due Today" or "Overdue":
      - Check if high-value customer
      - Send notification to seller
      - Send notification to admin
      - Increment notification counter
3. Log results to console and database
4. Return success status
```

**Output:**
```
Starting customer follow-up status update...
Processing 157 customers...
Finished! Updated 23 customer statuses. Sent 18 notifications.
```

**Key Notifications:**
- **Due Today:** Seller gets "🟡 Follow-up Due Today" notification
- **Overdue:** Seller gets "🔴 Overdue Follow-up" notification
- **High Value + Overdue 7+ days:** Admins also notified with "⭐ High Value Customer Alert"

---

## Notifications

### CustomerFollowUpReminder Notification

**File:** `app/Notifications/CustomerFollowUpReminder.php`

**Trigger:** Sent when customer status changes to "Due Today", "Overdue", or high-value alert

**Channels:** Database (in-app notification)

**Notification Types:**

| Type | Title | Message | Trigger |
|------|-------|---------|---------|
| `due` | 🟡 Follow-up Due Today | "It's the best time to contact {name}" | Status = "Due Today" |
| `overdue` | 🔴 Overdue Follow-up | "{name} is overdue. Last was {date}" | Status = "Overdue" |
| `high_value` | ⭐ High Value Customer Alert | "{name} (Priority #{rank}) hasn't ordered in a long time" | Priority > 100 & Overdue > 7 days |

**Notification Payload:**
```php
[
    'title' => '🟡 Follow-up Due Today',
    'message' => "It's the best time to contact John Doe for their next design task.",
    'customer_id' => 45,
    'action_url' => '/admin/customer-data-center/45',
    'type' => 'customer_follow_up'
]
```

**User Experience:**
- In-app bell icon shows notification count
- Click notification redirects to customer detail page
- Seller can immediately take action

---

## Usage Guide for Sellers

### Daily Workflow

**Morning Routine (6:00 AM - 8:00 AM):**

1. **Log in to Dashboard**
   - Navigate to `/admin/saler/my-dashboard`
   - Find "Predictive Sales Follow-up Stats" widget
   - Note counts for Due Today & Overdue

2. **Check Notifications**
   - Bell icon in header shows pending follow-ups
   - Click to see list of customers needing attention

3. **Visit Customer Data Center**
   - Click "Customer Data Center" or navigate to `/customer-data-center`
   - Default view shows "Due Today" + "Overdue" customers
   - Sorted by priority ranking (highest value first)

4. **Prioritize Contacts**
   - Focus on high-priority customers first (#ranking > 100)
   - These are historically your best customers
   - Highest potential for large orders

**Execution (8:00 AM - 5:00 PM):**

5. **Contact Customers**
   - Click "Direct Contact" for each customer
   - System opens WhatsApp link automatically
   - Send personalized message (product updates, special offers)
   - Jot down quick notes about response

6. **Log Follow-up Activity**
   - Return to system modal (still open)
   - Select action channel (should be "WhatsApp" if you just messaged)
   - Enter brief notes: "Customer interested in bulk T-shirts"
   - Optionally override next follow-up date if they gave specific timeline
   - Click "Save Log"

7. **Review Customer Details**
   - Click "View Insights" to see full customer profile
   - Check product/task type preferences
   - Reference past purchase amounts
   - Understand their business type

**Repeat for Next Customer:**
- Move to next in list
- Repeat steps 5-7

**End of Day:**
- System auto-generates tomorrow's list based on predictions
- No manual action needed
- Tomorrow morning, repeat the cycle

### Example: Customer Contact Scenario

**Customer:** Fatima's Boutique (Mahmoud Al-shami)
- Status: Due Today
- Priority: #87 (High-value)
- Avg Reorder: Every 22 days
- Last Order: Design task completed March 15 (18 days ago)
- Total Spent: 32,450 AED
- Task Types: T-shirt design, Embroidery

**Action Plan:**
1. Click "Direct Contact" button
2. WhatsApp opens: `https://wa.me/971501234567`
3. Send message: "Hi Mahmoud! 👋 Hope business is good! We have new design templates for bulk orders. When would be a good time for a quick call?"
4. Wait for response (maybe 1-2 hours)
5. Return to modal with their response
6. Select: Action = "WhatsApp"
7. Enter Notes: "Customer confirmed new order coming next week. Mentioned budget increase."
8. Override Date: "2026-04-10" (their suggested timeline)
9. Click "Save Log"
10. Click "View Insights" to see task-type predictions
11. Prepare quotes/samples for T-shirt design consultation

### Key Metrics to Monitor

**Personal KPIs (in Saler Dashboard):**
- Follow-ups Due Today: Should decrease as day progresses
- Follow-ups Completed: Track your activity
- Revenue from Contacted Customers: Monitor conversion rate
- Average Days to Follow-up: Should be < 7 days for priority customers

---

## API Reference

### GET /admin/customer-data-center

**List all customers (paginated)**

**Query Parameters:**
```
status=due|upcoming|new        (Default: due)
page=1                         (Pagination)
```

**Response (HTML):**
- Table of customers matching filter
- Stats card totals
- Pagination links

**cURL Example:**
```bash
curl -X GET "https://system.com/admin/customer-data-center?status=due&page=1" \
  -H "Authorization: Bearer TOKEN" \
  -H "Accept: text/html"
```

### GET /admin/customer-data-center/{customer_id}

**Detailed customer analytics**

**Response (HTML):**
- Full customer profile
- Purchase history timeline
- Product/task-type preferences
- All follow-up activities
- Smart prediction card
- Action buttons/modals

**cURL Example:**
```bash
curl -X GET "https://system.com/admin/customer-data-center/45" \
  -H "Authorization: Bearer TOKEN" \
  -H "Accept: text/html"
```

### POST /admin/customer-data-center/{customer_id}/follow-up

**Record a follow-up activity**

**Request Body:**
```json
{
    "action": "WhatsApp",
    "notes": "Customer confirmed order for next month",
    "next_follow_up_date": "2026-04-20"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Follow-up activity recorded successfully.",
    "redirect": "/admin/customer-data-center/45"
}
```

**Validation Rules:**
- `action`: Required, string
- `notes`: Optional, string
- `next_follow_up_date`: Optional, date, must be today or later

**cURL Example:**
```bash
curl -X POST "https://system.com/admin/customer-data-center/45/follow-up" \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "action": "Phone Call",
    "notes": "Discussed bulk pricing",
    "next_follow_up_date": "2026-04-15"
  }'
```

### PUT /admin/customer-data-center/{customer_id}/follow-up-date

**Manually set next follow-up date**

**Request Body:**
```json
{
    "manual_follow_up_date": "2026-04-22"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Next follow-up date has been manually scheduled.",
    "redirect": "/admin/customer-data-center/45"
}
```

**cURL Example:**
```bash
curl -X PUT "https://system.com/admin/customer-data-center/45/follow-up-date" \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "manual_follow_up_date": "2026-04-25"
  }'
```

### POST /admin/customer-data-center/{customer_id}/refresh

**Manually recalculate customer analytics**

**Response:**
```json
{
    "success": true,
    "message": "Analytics refreshed successfully.",
    "redirect": "/admin/customer-data-center/45"
}
```

**When to Use:**
- After adding new design tasks manually
- If customer data seems out of sync
- Before important decisions
- Testing/debugging

**cURL Example:**
```bash
curl -X POST "https://system.com/admin/customer-data-center/45/refresh" \
  -H "Authorization: Bearer TOKEN"
```

---

## Best Practices

### For Sellers

1. **Daily Routine is Essential**
   - Check follow-up list every morning
   - Contact "Due Today" customers before lunch
   - Contact "Overdue" customers immediately

2. **Use the Priority Ranking**
   - High priority (#) = High-value customers
   - Spend more time on top 20%
   - They drive 80% of revenue

3. **Always Log Activities**
   - Even if no sale, log the contact
   - System learns from patterns
   - Helps future predictions

4. **Leverage Manual Date Override**
   - If customer says "Call next Friday"
   - Set that specific date
   - System respects your input

5. **Review Task-Type Preferences**
   - Check what each customer commonly buys
   - Tailor your pitches accordingly
   - Prepare relevant samples/quotes

6. **Don't Spam**
   - If customer asks "don't contact for 2 weeks"
   - Manually set follow-up date to that date
   - Build trust = higher conversion

### For Administrators

1. **Monitor Scheduler Runs**
   - Check logs daily for errors
   - Verify notification counts trending correctly
   - Investigate zero-update days

2. **Audit Manual Overrides**
   - Sellers setting proper dates vs abusing
   - Use data center to verify reasonableness
   - Coach on patterns if needed

3. **Review Priority Calculations**
   - High-priority customers should correlate with sales
   - If not, adjust calculation formula
   - Balance spent & order count appropriately

4. **Troubleshoot Analytics Issues**
   - If customer status seems wrong, click "Refresh"
   - Check DesignTask statuses in database
   - Ensure only completed tasks are counted

5. **Customize Status Definitions**
   - Edit `determineStatus()` method based on business
   - You might want "Due in 7 days" instead of 3
   - Or adjust "Overdue" definition

6. **Regular Reporting**
   - Generate follow-up completion rates
   - Track seller adherence to process
   - Identify low-performing customers
   - Identify churn risks

### For System Maintenance

1. **Database Size Management**
   - Periodic archival of old follow-up records
   - Move 1+ year old activities to archive table
   - Keeps queries performant

2. **Analytics Accuracy**
   - Only count completed tasks (not drafts)
   - Ensure statuses are set correctly in DesignTask
   - Monitor for incomplete migrations

3. **Notification Tuning**
   - Edit notification messages for brand voice
   - Consider frequency caps (don't notify same seller 10x/day)
   - Test notifications before live deployment

4. **Performance Optimization**
   - Index heavily on `follow_up_status`, `priority_ranking`
   - Paginate lists (never fetch all customers)
   - Consider caching top 50 customers per seller

---

## Troubleshooting

### Issue: Customer Shows "New Customer" But Has Orders

**Cause:** DesignTask records not marked as "completed"

**Solution:**
1. Check DesignTask status in database
2. Ensure tasks are in one of: `completed`, `confirmed`, `printed`, `super_completed`
3. Click customer "Refresh" button to recalculate
4. Or run: `php artisan customers:update-follow-ups`

### Issue: Follow-up Date Doesn't Change

**Cause:** Manual date override is set and not yet passed

**Solution:**
- Manual date takes precedence over auto-calculated
- Wait until that date passes, or manually clear it
- Or set a new date via "Set Manual Date" button

### Issue: Seller Isn't Receiving Notifications

**Cause:** Notification channel not enabled or user has them disabled

**Solution:**
1. Check `config/queue.php` - ensure `QUEUE_CONNECTION=database`
2. Verify `notifications` table exists and isn't full
3. Check user's notification preferences
4. Run: `php artisan notifications:check-queue`

### Issue: Analytics Taking Too Long to Process

**Cause:** Large number of design tasks or slow database

**Solution:**
1. Add database indexes on `design_tasks(customer_id, status, created_at)`
2. Adjust scheduler to run during low-traffic hours
3. Consider splitting calculation into batches
4. Upgrade database hardware if possible

---

## Integration Points

### With DesignTask System
- Monitors `design_tasks.status` field
- Only completed tasks included in calculations
- `created_at` field used for interval calculation

### With User/Seller System
- `users.role` = 'saler' for permission checks
- Assigned seller tracks who added each customer
- Seller receives personalized notifications

### With Notification System
- Sends `CustomerFollowUpReminder` notifications
- Persists to `notifications` database table
- Displays in user notification bell

### With Saler Dashboard
- Shows top 5 urgent follow-ups widget
- Displays follow-up stats summary
- Links to full Customer Data Center

---

## Future Enhancements

1. **AI Prediction Models**
   - Machine learning to predict churn risk
   - Recommended discount/offer per customer
   - Optimal contact time prediction

2. **Multi-Channel Automation**
   - Auto-generate personalized email campaigns
   - SMS reminders via Beem Africa API
   - WhatsApp template messages

3. **Analytics Dashboards**
   - Revenue by follow-up status
   - Seller performance leaderboards
   - Customer lifetime value tracking

4. **Mobile App**
   - Mobile-optimized follow-up UI
   - Offline follow-up logging
   - Push notifications for due follow-ups

5. **Integration with CRM**
   - Sync to external CRM systems
   - Two-way data sync
   - Advanced customer segmentation

---

## Support & Questions

For issues or questions about the Customer Follow-up System:

1. Check this documentation
2. Review Recent Activity (Troubleshooting section)
3. Contact System Administrator
4. File bug report with reproduction steps

**Documentation Version:** 1.0
**Last Updated:** April 2, 2026
**Maintained By:** System Development Team

---

This is a comprehensive guide to the Customer Follow-up System. For development assistance or urgent issues, contact your system administrator.
