<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalerPerformanceExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    public function __construct(
        private $salers,
        private string $periodLabel = 'All Time'
    ) {}

    public function collection()
    {
        return $this->salers;
    }

    public function title(): string
    {
        return 'Sales Performance';
    }

    public function headings(): array
    {
        return [
            '#',
            'Salesperson Name',
            'Role',
            'Total Orders',
            'Completed Tasks',
            'Total Revenue (TZS)',
            'Status',
        ];
    }

    public function map($saler): array
    {
        static $i = 0;
        $i++;

        $totalTasks = $saler->design_tasks_count ?? $saler->designTasks->count();
        $totalOrders = $saler->saler_orders_count ?? $saler->salerOrders->count();
        $totalRevenue = $saler->designTasks->where('status', '!=', \App\Models\DesignTask::STATUS_CANCELLED)->sum('price');

        return [
            $i,
            $saler->name,
            ucfirst($saler->role),
            $totalOrders,
            $totalTasks,
            number_format($totalRevenue, 2),
            $saler->is_active ? 'Active' : 'Inactive',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 11]],
        ];
    }
}
