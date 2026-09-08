<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class CustomerExport extends DefaultValueBinder implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle, WithCustomValueBinder
{
    public function __construct(
        private $query,
        private string $sheetTitle = 'Customers'
    ) {}

    public function query()
    {
        return $this->query;
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }

    public function headings(): array
    {
        return [
            '#',
            'Name',
            'Phone',
            'Email',
            'Type',
            'Segment',
            'Status',
            'Purchase Count',
            'First Purchase',
            'Registered Date',
        ];
    }

    public function map($customer): array
    {
        static $i = 0;
        $i++;

        $firstPurchase = '—';
        if ($customer->first_purchase_date) {
            $firstPurchase = $customer->first_purchase_date instanceof \DateTimeInterface
                ? $customer->first_purchase_date->format('d M Y')
                : \Carbon\Carbon::parse($customer->first_purchase_date)->format('d M Y');
        }

        $createdAt = '—';
        if ($customer->created_at) {
            $createdAt = $customer->created_at instanceof \DateTimeInterface
                ? $customer->created_at->format('d M Y')
                : \Carbon\Carbon::parse($customer->created_at)->format('d M Y');
        }

        return [
            $i,
            $customer->name,
            $customer->phone,
            $customer->email,
            $customer->is_repeated ? 'Repeated' : 'New',
            $customer->is_wholesale ? 'Wholesale' : 'Retail',
            $customer->verified ? 'Verified' : 'Unverified',
            $customer->purchase_count ?? 0,
            $firstPurchase,
            $createdAt,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function bindValue(Cell $cell, $value)
    {
        if (is_string($value) && (str_starts_with($value, '+') || preg_match('/^\+?[0-9\s\-()]{7,}$/', $value))) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }
}
