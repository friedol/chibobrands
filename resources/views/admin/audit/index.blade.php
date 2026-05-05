@extends('layouts.admin')

@section('title', 'Transaction Control & Audit')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="fw-bold text-danger">Transaction Control & Audit</h2>
            <p class="text-muted">Detection of unbalanced transactions and payment mismatches</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Unbalanced Orders -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm border-top border-4 border-danger">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0 text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Unbalanced Orders</h5>
                    <small>Amount Paid + Balance ≠ Total Amount</small>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Order Code</th>
                                    <th>Total</th>
                                    <th>Paid + Balance</th>
                                    <th>Difference</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($unbalancedOrders as $order)
                                <tr>
                                    <td>{{ $order->order_code }}</td>
                                    <td>{{ number_format($order->total_amount) }}</td>
                                    <td>{{ number_format($order->amount_paid + $order->balance) }}</td>
                                    <td class="text-danger fw-bold">{{ number_format($order->total_amount - ($order->amount_paid + $order->balance)) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center py-4">No discrepancies found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mismatched Payment Status -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm border-top border-4 border-warning">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0 text-warning"><i class="fas fa-flag me-2"></i>Status/Payment Mismatch</h5>
                    <small>Status marked 'Paid' but Balance exists, or vice versa</small>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Order Code</th>
                                    <th>Status</th>
                                    <th>Balance</th>
                                    <th>Paid</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($mismatchedOrders as $order)
                                <tr>
                                    <td>{{ $order->order_code }}</td>
                                    <td>{{ ucfirst($order->payment_status) }}</td>
                                    <td class="{{ $order->balance > 0 ? 'text-danger fw-bold' : '' }}">{{ number_format($order->balance) }}</td>
                                    <td>{{ number_format($order->amount_paid) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center py-4">No mismatches found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
