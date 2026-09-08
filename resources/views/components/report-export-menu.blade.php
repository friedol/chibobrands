@props([
    'printUrl'    => null,
    'printJs'     => false,   // use window.print() when no printUrl
    'pdfUrl'      => null,
    'pdfJs'       => false,   // trigger browser print-to-PDF when no pdfUrl
    'excelUrl'    => null,
    'printTarget' => '_blank',
    'label'       => 'Export',
    'id'          => 'reportExportMenu' . \Illuminate\Support\Str::random(6),
])

@php
    $hasPrint = $printUrl || $printJs;
    $hasPdf   = $pdfUrl   || $pdfJs;
    $hasAny   = $hasPrint || $hasPdf || $excelUrl;
@endphp

<div class="dropdown d-inline-block">
    <button type="button"
            id="{{ $id }}"
            class="btn btn-outline-secondary btn-sm {{ $label ? 'px-3' : 'px-2' }}"
            data-bs-toggle="dropdown"
            aria-expanded="false"
            title="Export / Print">
        @if($label)
            <i class="fas fa-file-export me-1"></i><span>{{ $label }}</span><i class="fas fa-caret-down ms-1"></i>
        @else
            <i class="fas fa-ellipsis-v"></i>
        @endif
    </button>
    <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="{{ $id }}">

        {{-- Print --}}
        @if($hasPrint)
            <li>
                @if($printUrl)
                    <a class="dropdown-item" href="{{ $printUrl }}" target="{{ $printTarget }}">
                @else
                    <a class="dropdown-item" href="#" onclick="window.print(); return false;">
                @endif
                    <i class="fas fa-print me-2 text-dark"></i>Print
                </a>
            </li>
        @endif

        {{-- PDF --}}
        @if($hasPdf)
            @if($hasPrint)<li><hr class="dropdown-divider my-1"></li>@endif
            <li>
                @if($pdfUrl)
                    <a class="dropdown-item" href="{{ $pdfUrl }}">
                @else
                    <a class="dropdown-item" href="#" onclick="window.print(); return false;" title="Use browser's Save as PDF option">
                @endif
                    <i class="fas fa-file-pdf me-2 text-danger"></i>Export PDF
                </a>
            </li>
        @endif

        {{-- Excel --}}
        @if($excelUrl)
            @if($hasPrint || $hasPdf)<li><hr class="dropdown-divider my-1"></li>@endif
            <li>
                <a class="dropdown-item" href="{{ $excelUrl }}">
                    <i class="fas fa-file-excel me-2 text-success"></i>Export Excel
                </a>
            </li>
        @endif

        @if(!$hasAny)
            <li><span class="dropdown-item-text text-muted small">No export available</span></li>
        @endif
    </ul>
</div>
