<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\DesignTask;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'today');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        if ($dateFrom || $dateTo) {
            $period = 'custom';
            $dateFrom = $dateFrom ?: now()->format('Y-m-d');
            $dateTo = $dateTo ?: now()->format('Y-m-d');
        } else {
            switch ($period) {
                case 'today':
                    $dateFrom = now()->format('Y-m-d');
                    $dateTo = now()->format('Y-m-d');
                    break;
                case 'week':
                    $dateFrom = now()->startOfWeek()->format('Y-m-d');
                    $dateTo = now()->endOfWeek()->format('Y-m-d');
                    break;
                case 'month':
                    $dateFrom = now()->startOfMonth()->format('Y-m-d');
                    $dateTo = now()->endOfMonth()->format('Y-m-d');
                    break;
                case 'year':
                    $dateFrom = now()->startOfYear()->format('Y-m-d');
                    $dateTo = now()->endOfYear()->format('Y-m-d');
                    break;
                default:
                    $dateFrom = now()->format('Y-m-d');
                    $dateTo = now()->format('Y-m-d');
            }
        }

        $queryDateFrom = $dateFrom . ' 00:00:00';
        $queryDateTo = $dateTo . ' 23:59:59';

        // 1. Unbalanced Transactions
        $unbalancedOrders = Order::with('user')
            ->whereBetween('created_at', [$queryDateFrom, $queryDateTo])
            ->whereRaw('ABS(total_amount - (amount_paid + balance)) > 0.01')
            ->get();
        $unbalancedTasks = DesignTask::with('customer')
            ->whereBetween('created_at', [$queryDateFrom, $queryDateTo])
            ->whereRaw('ABS((CASE WHEN requires_receipt THEN price * 1.18 ELSE price END) - (amount_paid + balance)) > 0.01')
            ->get();

        // 2. FLAG Missing Payments (Potential Fraud or Loss)
        // Approved orders with 0 payment after 2 days (within period or still open)
        $missingOrderPayments = Order::with('user')->where('approval_status', 'approved')
            ->whereBetween('created_at', [$queryDateFrom, $queryDateTo])
            ->where('amount_paid', 0)
            ->where('created_at', '<', now()->subDays(2))
            ->get();
        
        // Completed items with outstanding balance
        $completedWithBalance = DesignTask::with('customer')->where('status', 'completed')
            ->whereBetween('created_at', [$queryDateFrom, $queryDateTo])
            ->where('balance', '>', 0)
            ->get();

        // 3. Status Mismatch
        $mismatchedOrders = Order::whereBetween('created_at', [$queryDateFrom, $queryDateTo])
            ->where(function($q) {
                $q->where(function($sub) {
                    $sub->where('payment_status', 'paid')->where('balance', '>', 0);
                })->orWhere(function($sub) {
                    $sub->where('payment_status', 'unpaid')->where('amount_paid', '>', 0);
                });
            })->get();

        return view('admin.finance.audit', compact(
            'unbalancedOrders', 'mismatchedOrders', 'unbalancedTasks', 
            'missingOrderPayments', 'completedWithBalance', 'period', 'dateFrom', 'dateTo'
        ));
    }

    public function print()
    {
        $unbalancedOrders = Order::with('user')->whereRaw('ABS(total_amount - (amount_paid + balance)) > 0.01')->get();
        $unbalancedTasks = DesignTask::with('customer')->whereRaw('ABS((CASE WHEN requires_receipt THEN price * 1.18 ELSE price END) - (amount_paid + balance)) > 0.01')->get();

        $missingOrderPayments = Order::with('user')->where('approval_status', 'approved')
            ->where('amount_paid', 0)
            ->where('created_at', '<', now()->subDays(2))
            ->get();
        
        $completedWithBalance = DesignTask::with('customer')->where('status', 'completed')
            ->where('balance', '>', 0)
            ->get();

        $mismatchedOrders = Order::where(function($q) {
            $q->where('payment_status', 'paid')->where('balance', '>', 0);
        })->orWhere(function($q) {
            $q->where('payment_status', 'unpaid')->where('amount_paid', '>', 0);
        })->get();

        return view('admin.finance.print-audit', compact(
            'unbalancedOrders', 'mismatchedOrders', 'unbalancedTasks', 
            'missingOrderPayments', 'completedWithBalance'
        ));
    }
}
