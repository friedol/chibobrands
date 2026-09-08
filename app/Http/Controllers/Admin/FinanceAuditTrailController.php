<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinanceAuditTrail;
use App\Models\User;
use App\Exports\SimpleArrayExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class FinanceAuditTrailController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'month');

        [$dateFrom, $dateTo] = match($period) {
            'today'    => [now()->startOfDay(), now()->endOfDay()],
            'week'     => [now()->startOfWeek(), now()->endOfWeek()],
            'month'    => [now()->startOfMonth(), now()->endOfMonth()],
            'year'     => [now()->startOfYear(), now()->endOfYear()],
            'custom'   => [
                $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : now()->startOfMonth(),
                $request->date_to   ? Carbon::parse($request->date_to)->endOfDay()     : now()->endOfMonth(),
            ],
            default    => [now()->startOfMonth(), now()->endOfMonth()],
        };

        $query = FinanceAuditTrail::with('user')->latest();

        $query->whereBetween('created_at', [$dateFrom, $dateTo]);

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->entity_type);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $entries = $query->paginate(30)->withQueryString();

        // Summary counts for the top cards
        $summary = [
            'total'      => FinanceAuditTrail::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'created'    => FinanceAuditTrail::where('action', 'created')->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'updated'    => FinanceAuditTrail::where('action', 'updated')->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'deleted'    => FinanceAuditTrail::where('action', 'deleted')->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'reconciled' => FinanceAuditTrail::where('action', 'reconciled')->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
        ];

        $users = User::whereIn('id',
            FinanceAuditTrail::distinct()->pluck('user_id')
        )->get(['id', 'name']);

        return view('admin.finance.finance-audit-trail', compact(
            'entries', 'summary', 'users', 'dateFrom', 'dateTo', 'period'
        ));
    }

    /**
     * Single source of truth for the unpaginated entry list behind Print/PDF/Excel.
     * Reuses index()'s filter/date-range resolution (via getData()) so all three
     * exports can never drift from what index() shows on screen.
     */
    private function getUnpaginatedEntries(Request $request): array
    {
        $data = $this->index($request)->getData();

        $entries = FinanceAuditTrail::with('user')
            ->whereBetween('created_at', [$data['dateFrom'], $data['dateTo']])
            ->when($request->filled('action'), fn($q) => $q->where('action', $request->action))
            ->when($request->filled('entity_type'), fn($q) => $q->where('entity_type', $request->entity_type))
            ->when($request->filled('user_id'), fn($q) => $q->where('user_id', $request->user_id))
            ->latest()
            ->get();

        return [
            'entries'  => $entries,
            'dateFrom' => $data['dateFrom'],
            'dateTo'   => $data['dateTo'],
            'period'   => $data['period'],
        ];
    }

    public function print(Request $request)
    {
        $request->merge(['_print' => true]);
        $data = $this->getUnpaginatedEntries($request);
        return view('admin.finance.print-finance-audit-trail', ['entries' => $data['entries']]);
    }

    public function pdf(Request $request)
    {
        $data = $this->getUnpaginatedEntries($request);
        $data['title'] = 'Finance Audit Trail Report';

        $filename = 'finance-audit-trail-report-'
            . Carbon::parse($data['dateFrom'])->format('Y-m-d') . '-to-'
            . Carbon::parse($data['dateTo'])->format('Y-m-d') . '.pdf';

        return Pdf::loadView('admin.reports.exports.finance-audit-trail', $data)
            ->download($filename);
    }

    public function excel(Request $request)
    {
        $data = $this->getUnpaginatedEntries($request);

        $headings = ['Date/Time', 'User', 'Action', 'Entity', 'Entity ID', 'Transaction Date', 'Old Value', 'New Value', 'Reason'];

        $rows = $data['entries']->map(function ($entry) {
            return [
                $entry->created_at->format('Y-m-d H:i:s'),
                $entry->user?->name ?? 'System',
                $entry->action_label,
                ucwords(str_replace('_', ' ', $entry->entity_type)),
                $entry->entity_id,
                $entry->transaction_date?->format('Y-m-d') ?? '',
                $entry->old_value ? json_encode($entry->old_value) : '',
                $entry->new_value ? json_encode($entry->new_value) : '',
                $entry->reason,
            ];
        })->toArray();

        $filename = 'finance-audit-trail-report-'
            . Carbon::parse($data['dateFrom'])->format('Y-m-d') . '-to-'
            . Carbon::parse($data['dateTo'])->format('Y-m-d') . '.xlsx';

        return Excel::download(new SimpleArrayExport($rows, $headings, 'Finance Audit Trail'), $filename);
    }
}
