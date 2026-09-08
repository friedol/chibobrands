<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Generic multi-sheet XLSX export.
 * Pass an array of sheet definitions:
 *   [['title' => 'Summary', 'headings' => [...], 'rows' => [...]],  ...]
 */
class MultiSheetReportExport implements WithMultipleSheets
{
    public function __construct(private array $sheets) {}

    public function sheets(): array
    {
        return array_map(
            fn($s) => new SimpleArrayExport($s['rows'], $s['headings'], $s['title'] ?? 'Sheet'),
            $this->sheets
        );
    }
}
