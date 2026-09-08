<?php

namespace App\Http\Controllers\Admin\Marketing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MarketingReportController extends Controller
{
    public function index()
    {
        return view('admin.marketing.reports.index');
    }

    public function print()
    {
        return view('admin.marketing.reports.print');
    }

    public function pdf()
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.marketing.reports.print')
            ->setPaper('a4', 'portrait');
        return $pdf->download('marketing-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function export(Request $request)
    {
        $headings = ['Report'];
        $rows     = [['Marketing reports export not yet configured.']];
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\SimpleArrayExport($rows, $headings, 'Marketing Report'),
            'marketing-report-' . now()->format('Y-m-d') . '.xlsx'
        );
    }
}
