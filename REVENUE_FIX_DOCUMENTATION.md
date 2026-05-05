# Revenue Calculation Fix - Superadmin Dashboard

## Issue Identified

The superadmin dashboard was showing **incorrect total revenue** because it was only counting:

1. Orders with `payment_status = 'paid'` (fully paid orders only)
2. Using `total_amount` instead of `amount_paid`
3. Design tasks only in specific statuses (completed, super_completed, printed)

This meant **partial payments were not included** in the revenue calculations, leading to underreported revenue figures.

## Changes Made

### File: `/app/Http/Controllers/AdminController.php`

#### 1. Main Revenue Calculation (Lines 97-101)

**Before:**

```php
$orderRevenue = $applyPeriod($applySalerScope(\App\Models\Order::query()))
    ->where('approval_status', 'approved')
    ->where('payment_status', 'paid')  // ❌ Only fully paid orders
    ->sum('total_amount') ?? 0;        // ❌ Total amount, not actual payments

$designRevenue = $applyPeriod(DesignTask::whereIn('status', [
    DesignTask::STATUS_COMPLETED,
    DesignTask::STATUS_SUPER_COMPLETED,
    DesignTask::STATUS_PRINTED
]))->sum('amount_paid') ?? 0;          // ❌ Only specific statuses
```

**After:**

```php
$orderRevenue = $applyPeriod($applySalerScope(\App\Models\Order::query()))
    ->where('approval_status', 'approved')
    ->sum('amount_paid') ?? 0;         // ✅ Sum actual payments received

$designRevenue = $applyPeriod(DesignTask::query())
    ->sum('amount_paid') ?? 0;         // ✅ All design task payments
```

#### 2. Period Stats (Line 133)

**Before:**

```php
'revenue' => $applyPeriod($applySalerScope(\App\Models\Order::where('approval_status', 'approved')
    ->where('payment_status', 'paid')))->sum('total_amount'),
```

**After:**

```php
'revenue' => $applyPeriod($applySalerScope(\App\Models\Order::where('approval_status', 'approved')))
    ->sum('amount_paid'),
```

#### 3. Today Stats (Line 143)

**Before:**

```php
'revenue' => $applySalerScope(\App\Models\Order::whereDate('created_at', $today)
    ->where('approval_status', 'approved')
    ->where('payment_status', 'paid'))->sum('total_amount'),
```

**After:**

```php
'revenue' => $applySalerScope(\App\Models\Order::whereDate('created_at', $today)
    ->where('approval_status', 'approved'))->sum('amount_paid'),
```

#### 4. Yesterday Stats (Line 152)

**Before:**

```php
'revenue' => $applySalerScope(\App\Models\Order::whereDate('created_at', $yesterday)
    ->where('approval_status', 'approved')
    ->where('payment_status', 'paid'))->sum('total_amount'),
```

**After:**

```php
'revenue' => $applySalerScope(\App\Models\Order::whereDate('created_at', $yesterday)
    ->where('approval_status', 'approved'))->sum('amount_paid'),
```

#### 5. Monthly Revenue Data (Lines 168-174, 190-196)

**Before:**

```php
$orderRev = $applySalerScope(\App\Models\Order::whereYear('created_at', $currentYear)
    ->whereMonth('created_at', $month)
    ->where('approval_status', 'approved')
    ->where('payment_status', 'paid'))
    ->sum('total_amount') ?? 0;

$designRev = \App\Models\DesignTask::whereYear('created_at', $currentYear)
    ->whereMonth('created_at', $month)
    ->whereIn('status', [DesignTask::STATUS_COMPLETED, DesignTask::STATUS_SUPER_COMPLETED, DesignTask::STATUS_PRINTED])
    ->sum('amount_paid') ?? 0;
```

**After:**

```php
$orderRev = $applySalerScope(\App\Models\Order::whereYear('created_at', $currentYear)
    ->whereMonth('created_at', $month)
    ->where('approval_status', 'approved'))
    ->sum('amount_paid') ?? 0;

$designRev = \App\Models\DesignTask::whereYear('created_at', $currentYear)
    ->whereMonth('created_at', $month)
    ->sum('amount_paid') ?? 0;
```

#### 6. Chart Data - Hourly (Lines 216-221)

**Before:**

```php
$revenueDataCurrent[] = $applySalerScope(\App\Models\Order::whereBetween('created_at', [...])
    ->where('approval_status', 'approved')
    ->where('payment_status', 'paid'))->sum('total_amount') ?? 0;

$designRevCurrent = DesignTask::whereBetween('created_at', [...])
    ->whereIn('status', [DesignTask::STATUS_COMPLETED, DesignTask::STATUS_SUPER_COMPLETED, DesignTask::STATUS_PRINTED])
    ->sum('amount_paid') ?? 0;
```

**After:**

```php
$revenueDataCurrent[] = $applySalerScope(\App\Models\Order::whereBetween('created_at', [...])
    ->where('approval_status', 'approved'))->sum('amount_paid') ?? 0;

$designRevCurrent = DesignTask::whereBetween('created_at', [...])
    ->sum('amount_paid') ?? 0;
```

#### 7. Chart Data - Daily (Lines 233-237)

**Before:**

```php
$revenueDataCurrent[] = $applySalerScope(\App\Models\Order::whereDate('created_at', $date->format('Y-m-d'))
    ->where('approval_status', 'approved')
    ->where('payment_status', 'paid'))->sum('total_amount') ?? 0;

$designRevCurrent = DesignTask::whereDate('created_at', $date->format('Y-m-d'))
    ->whereIn('status', [DesignTask::STATUS_COMPLETED, DesignTask::STATUS_SUPER_COMPLETED, DesignTask::STATUS_PRINTED])
    ->sum('amount_paid') ?? 0;
```

**After:**

```php
$revenueDataCurrent[] = $applySalerScope(\App\Models\Order::whereDate('created_at', $date->format('Y-m-d'))
    ->where('approval_status', 'approved'))->sum('amount_paid') ?? 0;

$designRevCurrent = DesignTask::whereDate('created_at', $date->format('Y-m-d'))
    ->sum('amount_paid') ?? 0;
```

#### 8. Chart Data - Monthly (Lines 249-253)

**Before:**

```php
$revenueDataCurrent[] = $applySalerScope(\App\Models\Order::whereYear('created_at', $month->year)
    ->whereMonth('created_at', $month->month)
    ->where('approval_status', 'approved')
    ->where('payment_status', 'paid'))->sum('total_amount') ?? 0;

$designRevCurrent = DesignTask::whereYear('created_at', $month->year)
    ->whereMonth('created_at', $month->month)
    ->whereIn('status', [DesignTask::STATUS_COMPLETED, DesignTask::STATUS_SUPER_COMPLETED, DesignTask::STATUS_PRINTED])
    ->sum('amount_paid') ?? 0;
```

**After:**

```php
$revenueDataCurrent[] = $applySalerScope(\App\Models\Order::whereYear('created_at', $month->year)
    ->whereMonth('created_at', $month->month)
    ->where('approval_status', 'approved'))->sum('amount_paid') ?? 0;

$designRevCurrent = DesignTask::whereYear('created_at', $month->year)
    ->whereMonth('created_at', $month->month)
    ->sum('amount_paid') ?? 0;
```

## Impact

### What Changed:

1. **Order Revenue**: Now counts `amount_paid` from all approved orders (including partial payments)
2. **Design Revenue**: Now counts `amount_paid` from all design tasks (not just completed ones)
3. **All Charts**: Updated to reflect actual payments received
4. **All Stats**: Updated to show accurate revenue figures

### What This Fixes:

- ✅ Partial payments are now included in revenue
- ✅ Revenue reflects actual money received, not invoiced amounts
- ✅ All design task payments are counted (not just completed tasks)
- ✅ Consistent revenue reporting across all dashboard metrics
- ✅ Accurate financial data for business decisions

### Example:

**Before:**

- Order total: TZS 1,000,000
- Amount paid: TZS 500,000 (partial payment)
- **Revenue shown: TZS 0** (because payment_status != 'paid')

**After:**

- Order total: TZS 1,000,000
- Amount paid: TZS 500,000 (partial payment)
- **Revenue shown: TZS 500,000** ✅ (actual payment received)

## Testing Recommendations

1. **Verify Total Revenue Card**: Check that the main revenue card shows the sum of all `amount_paid` from approved orders
2. **Check Charts**: Ensure all revenue charts reflect actual payments
3. **Compare Periods**: Test different period filters (today, week, month, year) to ensure consistency
4. **Partial Payments**: Create a test order with partial payment and verify it appears in revenue
5. **Design Tasks**: Verify design task payments are included in total revenue

## Database Schema Reference

### Orders Table:

- `total_amount`: Total order value
- `amount_paid`: Actual payment received
- `balance`: Remaining balance (total_amount - amount_paid)
- `payment_status`: 'pending', 'paid', 'partial', 'failed'
- `approval_status`: 'requested', 'approved', 'cancelled'

### Design Tasks Table:

- `price`: Total task price
- `amount_paid`: Actual payment received
- `balance`: Remaining balance (price - amount_paid)
- `status`: Various statuses (pending, in_progress, completed, etc.)

## Notes

- The fix maintains the `approval_status = 'approved'` filter for orders, ensuring only approved orders contribute to revenue
- Design tasks now count all payments regardless of status, which is more accurate for revenue tracking
- All chart data has been updated to maintain consistency across the dashboard
