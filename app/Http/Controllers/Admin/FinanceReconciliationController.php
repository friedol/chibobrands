<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\FinanceAuditTrail;
use App\Models\FinanceReconciliation;
use App\Models\Payment;
use App\Models\DesignTask;
use App\Services\FinanceHealthService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FinanceReconciliationController extends Controller
{
    public function __construct(private FinanceHealthService $health) {}

    // ── INDEX ──────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = FinanceReconciliation::with(['reconciledBy', 'customer', 'designTask'])
            ->latest('transaction_date');

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }

        $reconciliations = $query->paginate(25)->withQueryString();

        $summary = [
            'total'        => FinanceReconciliation::count(),
            'total_amount' => FinanceReconciliation::sum('amount'),
            'by_type'      => FinanceReconciliation::select('type', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
                                ->groupBy('type')->get(),
        ];

        $customers = Customer::orderBy('name')->get(['id', 'name']);

        return view('admin.finance.reconciliation.index', compact('reconciliations', 'summary', 'customers'));
    }

    // ── CREATE FORM ────────────────────────────────────────────

    public function create(Request $request)
    {
        $customers = Customer::orderBy('name')->get(['id', 'name', 'phone']);

        // Pre-fill from URL if coming from a mismatch fix
        $prefill = [
            'customer_id'    => $request->customer_id,
            'design_task_id' => $request->design_task_id,
            'payment_id'     => $request->payment_id,
            'amount'         => $request->amount,
        ];

        // If task was passed, load its details
        $task = null;
        if ($request->design_task_id) {
            $task = DesignTask::with('customer')->find($request->design_task_id);
        }

        return view('admin.finance.reconciliation.create', compact('customers', 'prefill', 'task'));
    }

    // ── STORE ──────────────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'      => 'nullable|exists:customers,id',
            'design_task_id'   => 'nullable|exists:design_tasks,id',
            'payment_id'       => 'nullable|exists:payments,id',
            'order_id'         => 'nullable|exists:orders,id',
            'amount'           => 'required|numeric|min:0.01',
            'transaction_date' => 'required|date|before_or_equal:today',
            'type'             => 'required|in:debt_write_off,partial_payment,full_payment,credit_note,adjustment,historical_entry',
            'reference'        => 'nullable|string|max:255',
            'notes'            => 'nullable|string',
            'reason'           => 'required|string|min:10',
        ]);

        $validated['reconciled_by']      = Auth::id();
        $validated['reconciliation_date'] = now()->toDateString();
        $validated['status']              = 'approved';

        $reconciliation = FinanceReconciliation::create($validated);

        // Write finance audit trail
        FinanceAuditTrail::record(
            action:          'reconciled',
            entityType:      'reconciliation',
            entityId:        $reconciliation->id,
            oldValue:        null,
            newValue:        [
                'amount'           => (float) $validated['amount'],
                'type'             => $validated['type'],
                'transaction_date' => $validated['transaction_date'],
                'customer_id'      => $validated['customer_id'] ?? null,
            ],
            reason:          $validated['reason'],
            transactionDate: $validated['transaction_date'],
        );

        // If linked to a payment, mark it as reconciled
        if (!empty($validated['payment_id'])) {
            $payment = Payment::find($validated['payment_id']);
            if ($payment) {
                $old = ['debt_status' => $payment->debt_status];
                $payment->debt_status         = Payment::DEBT_RECONCILED;
                $payment->reconciled_at        = now();
                $payment->reconciled_by        = Auth::id();
                $payment->reconciliation_note  = $validated['reason'];
                $payment->saveQuietly();

                FinanceAuditTrail::record(
                    action:          'reconciled',
                    entityType:      'payment',
                    entityId:        $payment->id,
                    oldValue:        $old,
                    newValue:        ['debt_status' => Payment::DEBT_RECONCILED],
                    reason:          $validated['reason'],
                    transactionDate: $validated['transaction_date'],
                );
            }
        }

        return redirect()
            ->route('admin.finance.reconciliation.index')
            ->with('success', 'Reconciliation recorded successfully. Audit trail updated.');
    }

    // ── SHOW (audit history) ───────────────────────────────────

    public function show(int $id)
    {
        $reconciliation = FinanceReconciliation::with(['reconciledBy', 'customer', 'designTask', 'payment'])
            ->findOrFail($id);

        $auditTrail = FinanceAuditTrail::where('entity_type', 'reconciliation')
            ->where('entity_id', $id)
            ->with('user')
            ->latest()
            ->get();

        return view('admin.finance.reconciliation.show', compact('reconciliation', 'auditTrail'));
    }

    // ── BULK FIX MISMATCHES ────────────────────────────────────

    /**
     * Automatically fix all payments that are marked pending
     * but the linked task balance is 0 (should be paid).
     */
    public function fixMismatches(Request $request)
    {
        $mismatches = $this->health->getDebtStatusMismatches();
        $fixed      = 0;

        foreach ($mismatches as $row) {
            $payment = Payment::find($row->payment_id);
            if ($payment) {
                $old = $payment->debt_status;
                $payment->debt_status = Payment::DEBT_PAID;
                $payment->saveQuietly();

                FinanceAuditTrail::record(
                    action:          'adjusted',
                    entityType:      'payment',
                    entityId:        $payment->id,
                    oldValue:        ['debt_status' => $old],
                    newValue:        ['debt_status' => Payment::DEBT_PAID],
                    reason:          'Auto-fixed: task balance is 0 but payment was still marked pending',
                    transactionDate: optional($payment->date)->toDateString(),
                );
                $fixed++;
            }
        }

        return response()->json(['fixed' => $fixed, 'message' => "$fixed payment(s) debt status corrected."]);
    }

    // ── CUSTOMER SEARCH (autocomplete) ─────────────────────────

    public function searchCustomers(Request $request)
    {
        $q = $request->get('q', '');
        $customers = Customer::where('name', 'like', "%$q%")
            ->orWhere('phone', 'like', "%$q%")
            ->limit(10)
            ->get(['id', 'name', 'phone']);

        return response()->json($customers);
    }

    // ── TASK SEARCH for reconciliation form ───────────────────

    public function searchTasks(Request $request)
    {
        $customerId = $request->customer_id;
        $tasks = DesignTask::where('customer_id', $customerId)
            ->where('balance', '>', 0)
            ->latest()
            ->limit(20)
            ->get(['id', 'task_code', 'price', 'amount_paid', 'balance']);

        return response()->json($tasks);
    }
}
