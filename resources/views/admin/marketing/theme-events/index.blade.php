@extends('layouts.admin')

@section('title', 'Theme Events')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Theme Events</h2>
        <a href="{{ route('admin.marketing.theme-events.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> New Event
        </a>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <p class="text-muted text-center py-5">No theme events scheduled.</p>
        </div>
    </div>
</div>
@endsection
