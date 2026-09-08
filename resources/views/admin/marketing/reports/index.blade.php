@extends('layouts.admin')

@section('title', 'Marketing Reports')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Marketing Reports</h2>
        <x-report-export-menu
            :print-url="route('admin.marketing.reports.print')"
            :pdf-url="route('admin.marketing.reports.pdf')"
            :excel-url="route('admin.marketing.reports.export')"
            print-target="_blank"
            label="Export"
        />
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <p class="text-muted text-center py-5">Report parameters will be configured here.</p>
        </div>
    </div>
</div>
@endsection
