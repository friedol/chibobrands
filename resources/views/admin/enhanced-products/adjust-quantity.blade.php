@extends('layouts.admin')

@section('title', 'Adjust Quantity')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.enhanced-products.index') }}">Enhanced Products</a></li>
                        <li class="breadcrumb-item active">Adjust Quantity</li>
                    </ol>
                </div>
                <h4 class="page-title">Adjust Quantity - {{ $enhancedProduct->name }} ({{ $enhancedProduct->barcode }})</h4>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Current Quantity</label>
                    <input type="text" class="form-control" value="{{ number_format($enhancedProduct->stock_quantity) }} {{ $enhancedProduct->stock_unit ?? 'pcs' }}" readonly>
                </div>
            </div>

            <form action="{{ route('admin.enhanced-products.quantity.update', $enhancedProduct->barcode) }}" method="POST">
                @csrf
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="adjustment" class="form-label">Adjustment (use negative to decrease)</label>
                        <input type="number" class="form-control" id="adjustment" name="adjustment" value="0" required>
                    </div>
                    <div class="col-md-8 d-flex gap-2">
                        <a href="{{ route('admin.enhanced-products.edit', $enhancedProduct->barcode) }}" class="btn btn-secondary">Back</a>
                        <button type="submit" class="btn btn-primary">Apply</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection


