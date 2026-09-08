<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

class ZohoComparisonService
{
    /**
     * Parse a Zoho CSV/Excel export into a normalized Collection.
     * Expected columns (case-insensitive, flexible order):
     *   Date, Customer/Account, Amount/Total, Reference/Invoice, Type
     */
    public function importZohoCsv(UploadedFile $file): Collection
    {
        $path    = $file->getRealPath();
        $rows    = [];
        $headers = [];

        if ($file->getClientOriginalExtension() === 'csv') {
            $handle = fopen($path, 'r');
            $lineNo = 0;
            while (($line = fgetcsv($handle)) !== false) {
                if ($lineNo === 0) {
                    $headers = array_map(fn($h) => strtolower(trim($h)), $line);
                } else {
                    $rows[] = $this->normalizeCsvRow($headers, $line);
                }
                $lineNo++;
            }
            fclose($handle);
        } else {
            // Return empty with error flag for non-CSV
            return collect([['error' => 'Only CSV files are supported for Zoho import.']]);
        }

        return collect($rows)->filter(fn($r) => !empty($r['date']) && !empty($r['amount']));
    }

    /**
     * Compare Zoho rows with our system data.
     * Returns: ['matched', 'discrepancies', 'only_in_zoho', 'only_in_system', 'summary']
     */
    public function compare(Collection $zohoRows, string $dateFrom, string $dateTo): array
    {
        $systemData = $this->getSystemDailySummaries($dateFrom, $dateTo);
        $zohoData   = $this->groupZohoByDate($zohoRows);

        $allDates   = collect(array_keys($systemData))
            ->merge(array_keys($zohoData))
            ->unique()
            ->sort()
            ->values();

        $matched       = [];
        $discrepancies = [];
        $onlyInZoho    = [];
        $onlyInSystem  = [];

        foreach ($allDates as $date) {
            $sys  = $systemData[$date]  ?? null;
            $zoho = $zohoData[$date]    ?? null;

            if ($sys && $zoho) {
                $diff = abs($sys['total'] - $zoho['total']);
                if ($diff < 1) {
                    $matched[] = [
                        'date'          => $date,
                        'system_total'  => $sys['total'],
                        'zoho_total'    => $zoho['total'],
                        'difference'    => 0,
                    ];
                } else {
                    $discrepancies[] = [
                        'date'          => $date,
                        'system_total'  => $sys['total'],
                        'zoho_total'    => $zoho['total'],
                        'difference'    => $sys['total'] - $zoho['total'],
                    ];
                }
            } elseif (!$sys && $zoho) {
                $onlyInZoho[] = ['date' => $date, 'zoho_total' => $zoho['total']];
            } elseif ($sys && !$zoho) {
                $onlyInSystem[] = ['date' => $date, 'system_total' => $sys['total']];
            }
        }

        return [
            'matched'        => collect($matched),
            'discrepancies'  => collect($discrepancies),
            'only_in_zoho'   => collect($onlyInZoho),
            'only_in_system' => collect($onlyInSystem),
            'summary' => [
                'total_days'        => count($allDates),
                'matched_days'      => count($matched),
                'discrepancy_days'  => count($discrepancies),
                'only_zoho_days'    => count($onlyInZoho),
                'only_system_days'  => count($onlyInSystem),
                'total_system'      => collect($systemData)->sum('total'),
                'total_zoho'        => $zohoRows->sum('amount'),
                'net_difference'    => collect($systemData)->sum('total') - $zohoRows->sum('amount'),
            ],
        ];
    }

    // ── Private Helpers ────────────────────────────────────────

    private function normalizeCsvRow(array $headers, array $values): array
    {
        $map = array_combine($headers, array_pad($values, count($headers), ''));

        $amountKeys   = ['amount', 'total', 'grand total', 'credit', 'debit'];
        $dateKeys     = ['date', 'transaction date', 'invoice date'];
        $refKeys      = ['reference', 'invoice number', 'invoice no', 'ref'];
        $customerKeys = ['account', 'customer', 'customer name', 'account name'];

        $amount = 0;
        foreach ($amountKeys as $k) {
            if (isset($map[$k]) && is_numeric(str_replace(',', '', $map[$k]))) {
                $amount = (float) str_replace(',', '', $map[$k]);
                break;
            }
        }

        $date = '';
        foreach ($dateKeys as $k) {
            if (!empty($map[$k])) {
                try {
                    $date = \Carbon\Carbon::parse($map[$k])->toDateString();
                } catch (\Exception $e) {
                    $date = $map[$k];
                }
                break;
            }
        }

        $reference = '';
        foreach ($refKeys as $k) {
            if (!empty($map[$k])) { $reference = $map[$k]; break; }
        }

        $customer = '';
        foreach ($customerKeys as $k) {
            if (!empty($map[$k])) { $customer = $map[$k]; break; }
        }

        return compact('date', 'amount', 'reference', 'customer') + ['raw' => $map];
    }

    private function groupZohoByDate(Collection $rows): array
    {
        return $rows->groupBy('date')->map(function ($group) {
            return ['total' => $group->sum('amount'), 'count' => $group->count()];
        })->toArray();
    }

    private function getSystemDailySummaries(string $from, string $to): array
    {
        $results = \DB::table('payments')
            ->selectRaw('DATE(date) as day, SUM(amount) as total, COUNT(*) as count')
            ->whereBetween('date', [$from, $to])
            ->groupBy('day')
            ->get();

        $map = [];
        foreach ($results as $row) {
            $map[$row->day] = ['total' => (float) $row->total, 'count' => $row->count];
        }
        return $map;
    }
}
