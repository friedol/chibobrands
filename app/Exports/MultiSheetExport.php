<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Generic wrapper for exporting several pre-built sheet objects (e.g. SimpleArrayExport
 * instances) as one .xlsx workbook. Use this instead of writing a bespoke
 * WithMultipleSheets class when the sheets are already built.
 */
class MultiSheetExport implements WithMultipleSheets
{
    public function __construct(private array $sheets) {}

    public function sheets(): array
    {
        return $this->sheets;
    }
}
