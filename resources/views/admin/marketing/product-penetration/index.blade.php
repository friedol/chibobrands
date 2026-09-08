@extends('layouts.admin')

@section('title', 'Product Penetration')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 fw-bold">Product Penetration</h2>
        <a href="{{ route('admin.marketing.product-penetration.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> New Penetration Activity
        </a>
    </div>

    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Item</th>
                            <th>Type</th>
                            <th>Market Segment</th>
                            <th class="text-center">Current %</th>
                            <th class="text-center">Target %</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                            <tr>
                                <td class="ps-4 fw-semibold">
                                    @if($record->item_type === 'task_type')
                                        <i class="fas fa-paint-brush me-1" style="color:#8b5cf6;"></i>
                                        {{ $record->taskType->name ?? '—' }}
                                    @else
                                        <i class="fas fa-box me-1 text-warning"></i>
                                        {{ $record->product->name ?? '—' }}
                                    @endif
                                </td>
                                <td>
                                    @if($record->item_type === 'task_type')
                                        <span class="badge" style="background:rgba(139,92,246,.1);color:#7c3aed;">Design Task</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning-emphasis">Product</span>
                                    @endif
                                </td>
                                <td>{{ $record->target_segment ?: '—' }}</td>
                                <td class="text-center">{{ number_format($record->current_penetration, 1) }}%</td>
                                <td class="text-center">{{ number_format($record->target_penetration, 1) }}%</td>
                                <td>
                                    @php
                                        $badge = match($record->status) {
                                            'active'    => 'bg-success',
                                            'completed' => 'bg-primary',
                                            'on_hold'   => 'bg-secondary',
                                            default     => 'bg-warning text-dark',
                                        };
                                    @endphp
                                    <span class="badge {{ $badge }}">{{ ucfirst(str_replace('_',' ',$record->status)) }}</span>
                                </td>
                                <td class="text-muted small">{{ $record->created_at->format('d M Y') }}</td>
                                <td>
                                    <form action="{{ route('admin.marketing.product-penetration.destroy', $record) }}" method="POST"
                                          onsubmit="return confirm('Delete this record?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger px-2 py-1">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="fas fa-box-open text-muted mb-2 d-block" style="font-size:2rem;opacity:.4;"></i>
                                    <p class="text-muted mb-0">No product penetration data yet.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
