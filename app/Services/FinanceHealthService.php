<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\DesignTask;
use App\Models\Order;
use App\Models\Customer;
use App\Models\FinanceReconciliation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class FinanceHealthService
{
    // ── 1. Outstanding Balances ────────────────────────────────

    /**
     * Customers with outstanding (unpaid) design task balances.
     * Returns a collection sorted by largest debt first.
     */
    public function getOutstandingBalances(): Collection
    {
        return DB::table('design_tasks as dt')
            ->join('customers as c', 'c.id', '=', 'dt.customer_id')
            ->select(
                'c.id as customer_id',
                'c.name as customer_name',
                'c.phone',
                DB::raw('COUNT(dt.id) as task_count'),
                DB::raw('SUM(dt.price) as total_billed'),
                DB::raw('SUM(dt.amount_paid) as total_paid'),
                DB::raw('SUM(dt.balance) as total_outstanding'),
            )
            ->whereNull('dt.deleted_at')
            ->where('dt.balance', '>', 0)
            ->whereNotIn('dt.status', ['cancelled'])
            ->groupBy('c.id', 'c.name', 'c.phone')
            ->orderByDesc('total_outstanding')
            ->get();
    }

    // ── 2. Duplicate Payments ──────────────────────────────────

    /**
     * Detect potential duplicate payments:
     * Same customer_id + same amount + same date (within 1 day).
     */
    public function detectDuplicatePayments(): Collection
    {
        $raw = DB::table('payments')
            ->select(
                'customer_id',
                'amount',
                DB::raw('DATE(date) as payment_date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('GROUP_CONCAT(id ORDER BY id) as payment_ids'),
                DB::raw('GROUP_CONCAT(payment_method ORDER BY id) as methods'),
            )
            ->whereNotNull('customer_id')
            ->groupBy('customer_id', 'amount', DB::raw('DATE(date)'))
            ->having('count', '>', 1)
            ->orderByDesc('count')
            ->get();

        if ($raw->isEmpty()) return collect();

        $customerIds = $raw->pluck('customer_id')->unique()->toArray();
        $customers   = Customer::whereIn('id', $customerIds)->pluck('name', 'id');

        return $raw->map(function ($row) use ($customers) {
            $row->customer_name = $customers[$row->customer_id] ?? '(Unknown)';
            return $row;
        });
    }

    // ── 3. Missing Transactions ────────────────────────────────

    /**
     * Tasks where amount_paid > 0 but NO corresponding payments row exists.
     */
    public function detectMissingTransactions(): Collection
    {
        return DB::table('design_tasks as dt')
            ->leftJoin('payments as p', 'p.design_task_id', '=', 'dt.id')
            ->join('customers as c', 'c.id', '=', 'dt.customer_id')
            ->select(
                'dt.id',
                'dt.task_code',
                'dt.price',
                'dt.amount_paid',
                'dt.balance',
                'c.name as customer_name',
                'c.phone',
                DB::raw('COUNT(p.id) as payment_count'),
            )
            ->whereNull('dt.deleted_at')
            ->where('dt.amount_paid', '>', 0)
            ->groupBy('dt.id', 'dt.task_code', 'dt.price', 'dt.amount_paid', 'dt.balance', 'c.name', 'c.phone')
            ->having('payment_count', '=', 0)
            ->orderByDesc('dt.amount_paid')
            ->get();
    }

    // ── 4. Debt Status Mismatches ──────────────────────────────

    /**
     * Payments marked is_debt=1 with debt_status='pending'
     * but the linked task balance is 0 (should be 'paid').
     */
    public function getDebtStatusMismatches(): Collection
    {
        return DB::table('payments as p')
            ->join('design_tasks as dt', 'dt.id', '=', 'p.design_task_id')
            ->join('customers as c', 'c.id', '=', 'p.customer_id')
            ->select(
                'p.id as payment_id',
                'p.amount',
                'p.debt_status',
                'p.date',
                'dt.id as task_id',
                'dt.task_code',
                'dt.balance as task_balance',
                'c.name as customer_name',
            )
            ->whereNull('dt.deleted_at')
            ->where('p.is_debt', true)
            ->where('p.debt_status', Payment::DEBT_PENDING)
            ->where('dt.balance', '<=', 0)
            ->get();
    }

    // ── 5. Reconciliation Status ───────────────────────────────

    public function getReconciliationSummary(): array
    {
        $byType = FinanceReconciliation::select('type', DB::raw('count(*) as count'), DB::raw('SUM(amount) as total'))
            ->groupBy('type')
            ->get()
            ->keyBy('type');

        return [
            'total_reconciliations' => FinanceReconciliation::count(),
            'total_amount'          => FinanceReconciliation::sum('amount'),
            'by_type'               => $byType,
            'pending_review'        => FinanceReconciliation::where('status', 'pending_review')->count(),
        ];
    }

    // ── 6. Health Score ────────────────────────────────────────

    /**
     * Returns a 0–100 health score based on:
     * - % debt payments correctly marked paid  (40%)
     * - % tasks with no balance mismatch       (30%)
     * - % payments with no duplicates          (20%)
     * - % tasks with matching payment rows     (10%)
     */
    public function computeHealthScore(): array
    {
        $totalDebt      = Payment::where('is_debt', true)->count();
        $correctlyPaid  = Payment::where('is_debt', true)->where('debt_status', Payment::DEBT_PAID)->count();
        $debtScore      = $totalDebt > 0 ? round(($correctlyPaid / $totalDebt) * 40) : 40;

        $totalTasks     = DesignTask::where('balance', '>', 0)->count();
        $mismatches     = $this->getDebtStatusMismatches()->count();
        $taskScore      = $totalTasks > 0 ? max(0, 30 - round(($mismatches / max($totalTasks, 1)) * 30)) : 30;

        $duplicates     = $this->detectDuplicatePayments()->count();
        $totalPayments  = Payment::count();
        $dupScore       = $totalPayments > 0 ? max(0, 20 - round(($duplicates / max($totalPayments, 1)) * 20)) : 20;

        $missing        = $this->detectMissingTransactions()->count();
        $missingScore   = max(0, 10 - min($missing, 10));

        $total = $debtScore + $taskScore + $dupScore + $missingScore;

        return [
            'score'          => $total,
            'grade'          => $this->scoreGrade($total),
            'color'          => $this->scoreColor($total),
            'debt_score'     => $debtScore,
            'task_score'     => $taskScore,
            'dup_score'      => $dupScore,
            'missing_score'  => $missingScore,
        ];
    }

    // ── Finance Summary (for Zoho comparison) ─────────────────

    public function getDailySummary(string $date): array
    {
        $start = $date . ' 00:00:00';
        $end   = $date . ' 23:59:59';

        return [
            'date'             => $date,
            'total_collected'  => Payment::whereBetween('date', [$start, $end])->sum('amount'),
            'total_billed'     => DesignTask::whereBetween('created_at', [$start, $end])->sum('price'),
            'total_expenses'   => DB::table('expenses')->whereBetween('date', [$start, $end])->sum('amount'),
            'payment_count'    => Payment::whereBetween('date', [$start, $end])->count(),
        ];
    }

    // ── Private Helpers ────────────────────────────────────────

    private function scoreGrade(int $score): string
    {
        return match(true) {
            $score >= 90 => 'Excellent',
            $score >= 75 => 'Good',
            $score >= 60 => 'Fair',
            $score >= 40 => 'Poor',
            default      => 'Critical',
        };
    }

    private function scoreColor(int $score): string
    {
        return match(true) {
            $score >= 90 => 'success',
            $score >= 75 => 'info',
            $score >= 60 => 'warning',
            default      => 'danger',
        };
    }
}
