<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

// ── Main export (multiple sheets) ─────────────────────────────────────────

class SalesReportExport implements WithMultipleSheets
{
    public function __construct(
        private array   $data,
        private array   $ranking,
        private Carbon  $from,
        private Carbon  $to,
        private string  $period,
        private string  $sellerName,
    ) {}

    public function sheets(): array
    {
        $sheets = [
            new SalesReportSummarySheet($this->data, $this->from, $this->to, $this->period, $this->sellerName),
            new SalesReportSourceSheet($this->data['sourceStats'], $this->from, $this->to),
        ];

        if (!empty($this->ranking)) {
            $sheets[] = new SalesReportRankingSheet($this->ranking, $this->from, $this->to);
        }

        return $sheets;
    }
}

// ── Sheet 1: Summary KPIs ──────────────────────────────────────────────────

class SalesReportSummarySheet implements FromArray, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(
        private array  $data,
        private Carbon $from,
        private Carbon $to,
        private string $period,
        private string $sellerName,
    ) {}

    public function title(): string { return 'Summary'; }

    public function array(): array
    {
        $d = $this->data;

        return [
            ['CHIBO BRANDS — Sales Performance Report'],
            ['Period', ucfirst($this->period), $this->from->format('d M Y') . ' → ' . $this->to->format('d M Y')],
            ['Seller', $this->sellerName],
            ['Generated', Carbon::now()->format('d M Y H:i')],
            [],
            ['── LEADS ──'],
            ['Metric', 'Value'],
            ['Total Leads', $d['totalLeads']],
            ['Converted', $d['convertedLeads']],
            ['Pending', $d['pendingLeads']],
            ['Not Interested', $d['notInterested']],
            ['Follow-Ups Done', $d['followUpsDone']],
            ['Conversion Rate', $d['totalLeads'] > 0
                ? round(($d['convertedLeads'] / $d['totalLeads']) * 100, 1) . '%'
                : '0%'],
            [],
            ['── REVENUE ──'],
            ['Metric', 'Value (TZS)'],
            ['Total Revenue Collected', number_format($d['totalRevenue'])],
            ['Total Billed', number_format($d['totalBilled'])],
            ['New Customer Revenue', number_format($d['newRevenue'])],
            ['Repeated Customer Revenue', number_format($d['repRevenue'])],
            [],
            ['── TASKS ──'],
            ['Metric', 'Value'],
            ['Paid Tasks', $d['paidTasks']],
            ['Unpaid Tasks (Balance Due)', $d['unpaidTasks']],
            [],
            ['── CUSTOMERS ──'],
            ['Metric', 'Value'],
            ['New Customers (Period)', $d['newCustomerCount']],
            ['Repeated Customers (Period)', $d['repCustomerCount']],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1   => ['font' => ['bold' => true, 'size' => 14], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C0392B']], 'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']]],
            7   => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']]],
            16  => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']]],
            23  => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']]],
            28  => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']]],
        ];
    }
}

// ── Sheet 2: Lead Source Breakdown ─────────────────────────────────────────

class SalesReportSourceSheet implements FromArray, WithTitle, WithHeadings, WithStyles, ShouldAutoSize
{
    public function __construct(
        private array  $sourceStats,
        private Carbon $from,
        private Carbon $to,
    ) {}

    public function title(): string { return 'Source Breakdown'; }

    public function headings(): array
    {
        return ['Source', 'Total Leads', 'Converted (Paid)', 'Pending (Unpaid)', 'Revenue (TZS)'];
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->sourceStats as $source => $stat) {
            $rows[] = [
                ucwords(str_replace('_', ' ', $source)),
                $stat['total'],
                $stat['paid'],
                $stat['unpaid'],
                number_format($stat['revenue']),
            ];
        }

        if (empty($rows)) {
            $rows[] = ['No data for selected period', '', '', '', ''];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C0392B']], 'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']]],
        ];
    }
}

// ── Sheet 3: Seller Rankings ───────────────────────────────────────────────

class SalesReportRankingSheet implements FromArray, WithTitle, WithHeadings, WithStyles, ShouldAutoSize
{
    public function __construct(
        private array  $ranking,
        private Carbon $from,
        private Carbon $to,
    ) {}

    public function title(): string { return 'Seller Rankings'; }

    public function headings(): array
    {
        return ['Rank', 'Seller', 'Revenue (TZS)', 'Leads', 'Converted', 'Conversion Rate %'];
    }

    public function array(): array
    {
        return array_map(function ($seller, $index) {
            return [
                $index + 1,
                $seller['name'],
                number_format($seller['revenue']),
                $seller['leads'],
                $seller['converted'],
                $seller['rate'] . '%',
            ];
        }, $this->ranking, array_keys($this->ranking));
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2C3E50']], 'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']]],
        ];
    }
}
