<?php

namespace App\Observers;

use App\Models\Payment;
use App\Models\FinanceAuditTrail;

class PaymentObserver
{
    /**
     * After a payment is created, sync the debt status of the linked task/order.
     * Also write a finance audit trail entry.
     */
    public function created(Payment $payment): void
    {
        // Sync debt status on the payment itself
        $payment->syncDebtStatus();

        // If linked to a design task, sync all sibling debt payments for that task
        if ($payment->design_task_id && $task = $payment->designTask) {
            $task->syncDebtStatusOnPayments();
        }

        // Audit trail
        FinanceAuditTrail::record(
            action:          'created',
            entityType:      'payment',
            entityId:        $payment->id,
            oldValue:        null,
            newValue:        [
                'amount'         => (float) $payment->amount,
                'payment_method' => $payment->payment_method,
                'is_debt'        => (bool) $payment->is_debt,
                'debt_status'    => $payment->debt_status,
                'date'           => optional($payment->date)->toDateString(),
            ],
            reason:          'Payment recorded via system',
            transactionDate: optional($payment->date)->toDateString(),
        );
    }

    /**
     * After a payment is updated, re-sync status and log changes.
     */
    public function updated(Payment $payment): void
    {
        $dirty = $payment->getDirty();
        if (empty($dirty)) return;

        $original = [];
        $changes  = [];
        foreach ($dirty as $key => $newVal) {
            $original[$key] = $payment->getOriginal($key);
            $changes[$key]  = $newVal;
        }

        // Sync debt status
        $payment->syncDebtStatus();
        if ($payment->design_task_id && $task = $payment->designTask) {
            $task->syncDebtStatusOnPayments();
        }

        FinanceAuditTrail::record(
            action:          'updated',
            entityType:      'payment',
            entityId:        $payment->id,
            oldValue:        $original,
            newValue:        $changes,
            reason:          'Payment updated via system',
            transactionDate: optional($payment->date)->toDateString(),
        );
    }

    /**
     * Before a payment is deleted, log the deletion for audit.
     */
    public function deleting(Payment $payment): void
    {
        FinanceAuditTrail::record(
            action:          'deleted',
            entityType:      'payment',
            entityId:        $payment->id,
            oldValue:        [
                'amount'         => (float) $payment->amount,
                'payment_method' => $payment->payment_method,
                'is_debt'        => (bool) $payment->is_debt,
                'debt_status'    => $payment->debt_status,
                'date'           => optional($payment->date)->toDateString(),
                'customer_id'    => $payment->customer_id,
            ],
            newValue:        null,
            reason:          'Payment deleted — balance will be recalculated',
            transactionDate: optional($payment->date)->toDateString(),
        );
    }

    /**
     * After deletion, re-sync debt status on the linked task (balance goes back up).
     */
    public function deleted(Payment $payment): void
    {
        if ($payment->design_task_id && $task = $payment->designTask) {
            $task->syncDebtStatusOnPayments();
        }
    }
}
