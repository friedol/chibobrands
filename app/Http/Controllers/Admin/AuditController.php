<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\DesignTask;
use App\Exports\SimpleArrayExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AuditController extends Controller
{
    /**
     * Single source of truth for the Financial Audit report.
     * index(), print(), pdf() and excel() all call this so Print/PDF/Excel
     * can never drift from what's shown on screen (they previously each ran
     * their own copy of these five queries independently).
     */
    private function buildAuditData(Request $request): array
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
        // Approved orders with 0 payment that are older than 2 days
        $missingOrderPayments = Order::with('user')->where('approval_status', 'approved')
            ->whereBetween('created_at', [$queryDateFrom, $queryDateTo])
            ->where('amount_paid', 0)
            ->where('created_at', '<', now()->subDays(2)->endOfDay())
            ->get();

        // Tasks in any "done" status with an outstanding balance
        $completedWithBalance = DesignTask::with('customer')
            ->whereIn('status', ['completed', 'super_completed', 'confirmed', 'printing', 'printed'])
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

        return compact(
            'unbalancedOrders', 'mismatchedOrders', 'unbalancedTasks',
            'missingOrderPayments', 'completedWithBalance', 'period', 'dateFrom', 'dateTo'
        );
    }

    public function index(Request $request)
    {
        return view('admin.finance.audit', $this->buildAuditData($request));
    }

    public function print(Request $request)
    {
        return view('admin.finance.print-audit', $this->buildAuditData($request));
    }

    public function pdf(Request $request)
    {
        $data = $this->buildAuditData($request);
        $data['title'] = 'Financial Audit Report';

        $filename = 'finance-audit-report-' . $data['dateFrom'] . '-to-' . $data['dateTo'] . '.pdf';

        return Pdf::loadView('admin.reports.exports.finance-audit', $data)
            ->download($filename);
    }

    public function excel(Request $request)
    {
        $data = $this->buildAuditData($request);

        $headings = [
            'Category', 'Reference', 'Customer', 'Type', 'Finding',
            'Total / Expected', 'Recorded (Paid + Balance)', 'Outstanding / Gap',
        ];

        $rows = [];

        foreach ($data['missingOrderPayments'] as $order) {
            $rows[] = [
                'Missing Payment', $order->order_code, $order->user->name ?? 'Guest', 'Order',
                'No Payment Recorded', $order->total_amount, $order->amount_paid + $order->balance, $order->total_amount,
            ];
        }
        foreach ($data['completedWithBalance'] as $task) {
            $taskTotal = $task->requires_receipt ? $task->price * 1.18 : $task->price;
            $rows[] = [
                'Missing Payment', $task->task_code, $task->customer->name ?? 'N/A', 'Design Task',
                'Balance Unpaid', $taskTotal, $task->amount_paid + $task->balance, $task->balance,
            ];
        }
        foreach ($data['unbalancedOrders'] as $order) {
            $rows[] = [
                'Discrepancy', $order->order_code, $order->user->name ?? 'Guest', 'Order',
                'Amount discrepancy', $order->total_amount, $order->amount_paid + $order->balance,
                abs($order->total_amount - ($order->amount_paid + $order->balance)),
            ];
        }
        foreach ($data['unbalancedTasks'] as $task) {
            $taskTotal = $task->requires_receipt ? $task->price * 1.18 : $task->price;
            $rows[] = [
                'Discrepancy', $task->task_code, $task->customer->name ?? 'N/A', 'Design Task',
                'Amount discrepancy', $taskTotal, $task->amount_paid + $task->balance,
                abs($taskTotal - ($task->amount_paid + $task->balance)),
            ];
        }
        foreach ($data['mismatchedOrders'] as $order) {
            $finding = $order->payment_status === 'paid' && $order->balance > 0
                ? 'Marked PAID but has outstanding balance'
                : ($order->payment_status === 'unpaid' && $order->amount_paid > 0
                    ? 'Marked UNPAID but has recorded payments'
                    : 'Status/logic conflict');
            $rows[] = [
                'Status Conflict', $order->order_code, $order->user->name ?? 'Guest', 'Order',
                $finding, $order->amount_paid + $order->balance, $order->amount_paid, $order->balance,
            ];
        }

        $filename = 'finance-audit-report-' . $data['dateFrom'] . '-to-' . $data['dateTo'] . '.xlsx';

        return Excel::download(new SimpleArrayExport($rows, $headings, 'Finance Audit'), $filename);
    }
}
