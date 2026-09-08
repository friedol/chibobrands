<?php

namespace App\Support\Concerns;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * Sales targets can be recorded at any granularity (daily, weekly, monthly,
 * quarterly, yearly), independent of whatever date range a report is
 * currently filtered to. This trait resolves the target amount that is fair
 * to compare against a report's date range:
 *
 *   1. If a target exists at the exact granularity the report is viewing
 *      (e.g. a monthly target while viewing "This Month"), its recorded
 *      amount is used as-is — no scaling.
 *   2. Otherwise the closest available target (finest granularity first) is
 *      reduced to a daily rate (its own amount / its own day span) and that
 *      rate is multiplied by however many days the report range covers —
 *      e.g. a daily target of 1,200,000 becomes 1,200,000 x 7 when viewing
 *      "This Week" but no weekly target exists.
 */
trait ResolvesSalesTargets
{
    private function reportPeriodToTargetPeriod(?string $period): ?string
    {
        return match ($period) {
            'today', 'yesterday' => 'daily',
            'week'                => 'weekly',
            'month'               => 'monthly',
            'year'                => 'yearly',
            default               => null, // custom/6_months/2_years/all have no single matching granularity
        };
    }

    /**
     * @param Builder $scopeQuery A SalesTarget query already scoped to the target's
     *                            owner, e.g. SalesTarget::where('seller_id', $id) or
     *                            SalesTarget::where('department_id', $id)->whereNull('seller_id').
     * @return array{0: float, 1: ?string} [amount, human-readable note if the amount was scaled]
     */
    private function resolveSalesTarget(Builder $scopeQuery, Carbon $startDate, Carbon $endDate, ?string $period = null): array
    {
        $rangeStart = $startDate->copy()->startOfDay();
        $rangeEnd   = $endDate->copy()->startOfDay();
        $rangeDays  = max(1, $rangeStart->diffInDays($rangeEnd) + 1);

        // 1) Exact granularity match for the selected period — use the recorded amount directly.
        $matchPeriod = $this->reportPeriodToTargetPeriod($period);
        if ($matchPeriod) {
            $exact = (clone $scopeQuery)
                ->where('period', $matchPeriod)
                ->whereDate('start_date', '<=', $rangeEnd->toDateString())
                ->whereDate('end_date', '>=', $rangeStart->toDateString())
                ->orderByDesc('start_date')
                ->first();

            if ($exact) {
                return [(float) $exact->target_amount, null];
            }
        }

        // 2) No exact match — pick the finest-grained target on record (daily > weekly > monthly
        //    > quarterly > yearly) so the scaled daily rate is as accurate as possible.
        $target = null;
        foreach (['daily', 'weekly', 'monthly', 'quarterly', 'yearly'] as $granularity) {
            $target = (clone $scopeQuery)
                ->where('period', $granularity)
                ->whereDate('start_date', '<=', $rangeEnd->toDateString())
                ->orderByDesc('start_date')
                ->first();

            if ($target) {
                break;
            }
        }

        // Nothing has started yet as of the range — use the earliest upcoming one instead.
        if (!$target) {
            $target = (clone $scopeQuery)->orderBy('start_date')->first();
        }

        if (!$target) {
            return [0.0, null];
        }

        $targetStart    = Carbon::parse($target->start_date)->startOfDay();
        $targetEnd      = Carbon::parse($target->end_date)->startOfDay();
        $targetSpanDays = max(1, $targetStart->diffInDays($targetEnd) + 1);
        $dailyRate      = (float) $target->target_amount / $targetSpanDays;

        $amount = $dailyRate * $rangeDays;

        $note = null;
        if ($targetSpanDays !== $rangeDays) {
            if ($target->period === 'daily') {
                $note = 'Daily target (' . number_format($target->target_amount) . ') × ' . $rangeDays
                    . ' day' . ($rangeDays === 1 ? '' : 's') . ' = ' . number_format($amount);
            } else {
                $note = ucfirst($target->period) . ' target (' . number_format($target->target_amount) . ') scaled to '
                    . $rangeDays . ' day' . ($rangeDays === 1 ? '' : 's') . ' (' . number_format($dailyRate) . '/day)';
            }
        }

        return [$amount, $note];
    }
}
