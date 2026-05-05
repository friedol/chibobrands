@extends('layouts.admin')

@section('title', 'Admin Dashboard - CHIBO BRAND')

@push('styles')
<style>
/* Modern Light Dashboard Styles */
:root {
    --light-bg: #f8f9fa;
    --card-bg: #ffffff;
    --card-hover: #f8f9fa;
    --text-primary: #2c3e50;
    --text-secondary: #6c757d;
    --accent-red: #FF0000;
    --accent-green: #28a745;
    --accent-blue: #007bff;
    --accent-orange: #fd7e14;
    --accent-purple: #6f42c1;
    --border-color: #e9ecef;
}

body {
    background: var(--light-bg) !important;
    color: var(--text-primary);
}

.modern-dashboard {
    padding: 1.5rem;
    background: var(--light-bg);
    min-height: 100vh;
}

/* Header Section */
.dashboard-header {
    margin-bottom: 1.5rem;
}

.dashboard-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0 0 0.25rem 0;
}

.dashboard-subtitle {
    font-size: 0.8125rem;
    color: var(--text-secondary);
}

/* Stats Cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 0.875rem;
    margin-bottom: 1.25rem;
}

.stat-card {
    background: var(--card-bg);
    border-radius: 10px;
    padding: 1rem;
    transition: all 0.3s ease;
    border: 1px solid var(--border-color);
    position: relative;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--accent-red), var(--accent-orange));
    opacity: 0;
    transition: opacity 0.3s ease;
}

.stat-card:hover::before {
    opacity: 1;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    border-color: var(--accent-red);
}

.stat-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.stat-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.stat-icon.red { background: rgba(255, 0, 0, 0.1); color: var(--accent-red); }
.stat-icon.green { background: rgba(40, 167, 69, 0.1); color: var(--accent-green); }
.stat-icon.blue { background: rgba(0, 123, 255, 0.1); color: var(--accent-blue); }
.stat-icon.orange { background: rgba(253, 126, 20, 0.1); color: var(--accent-orange); }

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0.25rem 0;
    line-height: 1;
}

.stat-label {
    font-size: 0.8125rem;
    color: var(--text-secondary);
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.stat-change {
    font-size: 0.75rem;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-weight: 600;
}

.stat-change.positive {
    background: rgba(40, 167, 69, 0.1);
    color: var(--accent-green);
}

.stat-change.negative {
    background: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}

/* Chart Cards */
.chart-card {
    background: var(--card-bg);
    border-radius: 10px;
    padding: 1rem;
    border: 1px solid var(--border-color);
    margin-bottom: 1rem;
    height: 100%;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.chart-title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0;
}

.chart-subtitle {
    font-size: 0.75rem;
    color: var(--text-secondary);
    margin-top: 0.25rem;
}

/* Table Styles */
.modern-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.modern-table thead th {
    background: var(--light-bg);
    color: var(--text-secondary);
    font-weight: 600;
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 0.625rem;
    text-align: left;
    border-bottom: 2px solid var(--border-color);
}

.modern-table tbody td {
    padding: 0.625rem;
    color: var(--text-primary);
    border-bottom: 1px solid var(--border-color);
    font-size: 0.8125rem;
}

.modern-table tbody tr {
    transition: background 0.2s ease;
}

.modern-table tbody tr:hover {
    background: var(--card-hover);
}

/* Progress Bar */
.progress-bar-container {
    width: 100%;
    height: 6px;
    background: var(--border-color);
    border-radius: 3px;
    overflow: hidden;
    margin-top: 0.5rem;
}

.progress-bar-fill {
    height: 100%;
    border-radius: 4px;
    transition: width 1.5s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Earnings Circle */
.earnings-widget {
    text-align: center;
    padding: 2rem 1rem;
}

.earnings-circle {
    position: relative;
    width: 180px;
    height: 180px;
    margin: 0 auto 1.5rem;
}

.earnings-value {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
}

.earnings-percentage {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--text-primary);
    line-height: 1;
}

.earnings-label {
    font-size: 0.75rem;
    color: var(--text-secondary);
    margin-top: 0.5rem;
}

.earnings-amount {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}

.earnings-subtitle {
    font-size: 0.875rem;
    color: var(--text-secondary);
}

.earnings-growth {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.875rem;
    color: var(--accent-green);
    margin-top: 0.5rem;
}

/* Responsive Grid */
.dashboard-grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
}

.dashboard-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

@media (max-width: 1200px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 1024px) {
    .dashboard-grid-2 {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    }
}

/* ===== RESPONSIVE DESIGN ===== */

/* Tablet (≤1024px) */
@media (max-width: 1024px) {
    .modern-dashboard {
        padding: 1.5rem;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
    
    .dashboard-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
}

/* Tablet (≤768px) */
@media (max-width: 768px) {
    .modern-dashboard {
        padding: 1rem;
    }
    
    .dashboard-title {
        font-size: 1.75rem;
    }
    
    .dashboard-subtitle {
        font-size: 0.95rem;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.875rem;
    }
    
    .stat-card {
        padding: 1rem;
    }
    
    .stat-icon {
        width: 40px;
        height: 40px;
        font-size: 1.25rem;
    }
    
    .stat-value {
        font-size: 1.5rem;
    }
    
    .stat-label {
        font-size: 0.8125rem;
    }
    
    .stat-change {
        font-size: 0.75rem;
    }
    
    .dashboard-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .chart-card {
        padding: 1rem;
    }
    
    .chart-title {
        font-size: 1rem;
    }
    
    .chart-subtitle {
        font-size: 0.8125rem;
    }
    
    .modern-table {
        font-size: 0.8125rem;
    }
    
    .modern-table th,
    .modern-table td {
        padding: 0.625rem 0.5rem;
    }
}

/* Mobile (≤576px) */
@media (max-width: 576px) {
    .modern-dashboard {
        padding: 0.75rem;
    }
    
    .dashboard-title {
        font-size: 1.25rem;
        margin-bottom: 0.25rem;
    }
    
    .dashboard-subtitle {
        font-size: 0.8125rem;
    }
    
    /* Stack stats cards */
    .stats-grid {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
    
    .stat-card {
        padding: 0.875rem;
        display: flex;
        align-items: center;
        gap: 0.875rem;
    }
    
    .stat-header {
        margin-bottom: 0;
    }
    
    .stat-icon {
        width: 40px;
        height: 40px;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    
    .stat-content {
        flex: 1;
        text-align: left;
    }
    
    .stat-value {
        font-size: 1.25rem;
        margin-bottom: 0.125rem;
    }
    
    .stat-label {
        font-size: 0.75rem;
        margin-bottom: 0.125rem;
    }
    
    .stat-change {
        font-size: 0.6875rem;
    }
    
    /* Dashboard grid */
    .dashboard-grid {
        grid-template-columns: 1fr;
        gap: 0.75rem;
        margin-top: 0.75rem;
    }
    
    .chart-card {
        padding: 0.75rem;
        margin-bottom: 0.75rem;
    }
    
    .chart-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.375rem;
        margin-bottom: 0.625rem;
    }
    
    .chart-title {
        font-size: 0.875rem;
    }
    
    .chart-subtitle {
        font-size: 0.6875rem;
    }
    
    .btn {
        font-size: 0.8125rem;
        padding: 0.5rem 0.875rem;
    }
    
    .btn-sm {
        font-size: 0.75rem;
        padding: 0.375rem 0.625rem;
    }
    
    /* Table responsive */
    .modern-table {
        font-size: 0.6875rem;
    }
    
    .modern-table thead {
        display: none;
    }
    
    .modern-table tbody tr {
        display: block;
        margin-bottom: 0.875rem;
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 0.875rem;
        background: white;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }
    
    .modern-table tbody td {
        display: block;
        text-align: left !important;
        padding: 0.5rem 0;
        border: none;
    }
    
    .modern-table tbody td::before {
        content: attr(data-label);
        font-weight: 700;
        display: block;
        margin-bottom: 0.125rem;
        color: var(--text-secondary);
        font-size: 0.625rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .modern-table tbody td:first-child {
        padding-top: 0;
        font-size: 0.875rem;
        font-weight: 700;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 0.625rem;
        margin-bottom: 0.375rem;
    }
    
    .modern-table tbody td:first-child::before {
        display: none;
    }
    
    .modern-table tbody td:last-child {
        padding-bottom: 0;
        padding-top: 0.625rem;
        border-top: 1px solid var(--border-color);
        margin-top: 0.375rem;
    }
    
    /* Recent orders specific styles */
    .modern-table tbody td[data-label="Customer"] .fw-semibold {
        font-size: 0.875rem;
        color: var(--text-primary);
        display: block;
        margin-bottom: 0.25rem;
    }
    
    .modern-table tbody td[data-label="Total"] strong {
        font-size: 1rem;
        color: var(--chibo-success);
    }
    
    .modern-table .badge {
        font-size: 0.625rem;
        padding: 0.25rem 0.5rem;
    }
    
    .modern-table .btn-group {
        display: flex;
        gap: 0.375rem;
        flex-wrap: wrap;
    }
    
    .modern-table .btn-group .btn {
        flex: 0 0 auto;
        min-width: 36px;
        padding: 0.375rem;
        font-size: 0.625rem;
    }
    
    /* Progress bars */
    .progress {
        height: 6px;
    }
    
    /* Earnings widget */
    .earnings-chart {
        width: 70px;
        height: 70px;
    }
    
    .earnings-percentage {
        font-size: 1rem;
    }
    
    .earnings-amount {
        font-size: 1rem;
    }
    
    .earnings-subtitle {
        font-size: 0.625rem;
    }
}

/* Extra Small Mobile (≤375px) */
@media (max-width: 375px) {
    .modern-dashboard {
        padding: 0.5rem;
    }
    
    .dashboard-title {
        font-size: 1.125rem;
    }
    
    .dashboard-subtitle {
        font-size: 0.75rem;
    }
    
    .stat-card {
        padding: 0.75rem;
        gap: 0.625rem;
    }
    
    .stat-icon {
        width: 36px;
        height: 36px;
        font-size: 1.125rem;
    }
    
    .stat-value {
        font-size: 1.125rem;
    }
    
    .stat-label {
        font-size: 0.6875rem;
    }
    
    .chart-card {
        padding: 0.625rem;
    }
    
    .chart-title {
        font-size: 0.8125rem;
    }
    
    .btn {
        font-size: 0.6875rem;
        padding: 0.375rem 0.625rem;
    }
}

/* Landscape Orientation */
@media (max-height: 600px) and (orientation: landscape) {
    .stats-grid {
        grid-template-columns: repeat(4, 1fr);
    }
    
    .stat-card {
        padding: 0.625rem;
    }
    
    .stat-icon {
        width: 32px;
        height: 32px;
        font-size: 1rem;
    }
    
    .stat-value {
        font-size: 1.125rem;
    }
}

/* Badge Styles */
.badge {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.6875rem;
    font-weight: 600;
}

/* Chart Container */
.chart-container {
    position: relative;
    height: 300px;
    width: 100%;
}

/* Year Toggle Buttons */
.btn-group .btn.active {
    background-color: #FF0000;
    border-color: #FF0000;
    color: white;
}

.btn-group .btn:not(.active) {
    background-color: transparent;
    border-color: #FF0000;
    color: #FF0000;
}

.btn-group .btn:not(.active):hover {
    background-color: rgba(255, 0, 0, 0.1);
    border-color: #FF0000;
    color: #FF0000;
}

/* Chart Header Improvements */
.chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.chart-header .btn-group {
    flex-shrink: 0;
}

/* Auto-refresh indicator */
.auto-refresh-indicator {
    font-size: 0.75rem;
    color: var(--text-secondary);
}

.auto-refresh-indicator i {
    animation: spin 2s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Dashboard header improvements */
.dashboard-header .d-flex {
    flex-wrap: wrap;
    gap: 1rem;
}

@media (max-width: 768px) {
    .dashboard-header .d-flex {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .auto-refresh-indicator {
        align-self: flex-end;
    }
}

.bg-success {
    background-color: #28a745 !important;
    color: white;
}

.bg-warning {
    background-color: #ffc107 !important;
}

.bg-danger {
    background-color: #dc3545 !important;
    color: white;
}

.bg-secondary {
    background-color: #6c757d !important;
    color: white;
}

/* Button Styles */
.btn {
    padding: 0.3125rem 0.625rem;
    border-radius: 4px;
    font-size: 0.8125rem;
    font-weight: 500;
    border: 1px solid;
    transition: all 0.2s ease;
    text-decoration: none;
    display: inline-block;
}

.btn-sm {
    padding: 0.1875rem 0.375rem;
    font-size: 0.75rem;
}

.btn-primary {
    background-color: #007bff;
    border-color: #007bff;
    color: white;
}

.btn-primary:hover {
    background-color: #0056b3;
    border-color: #0056b3;
}

.btn-outline-primary {
    background-color: transparent;
    border-color: #007bff;
    color: #007bff;
}

.btn-outline-primary:hover {
    background-color: #007bff;
    color: white;
}

.btn-outline-success {
    background-color: transparent;
    border-color: #28a745;
    color: #28a745;
}

.btn-outline-success:hover {
    background-color: #28a745;
    color: white;
}

.btn-outline-danger {
    background-color: transparent;
    border-color: #dc3545;
    color: #dc3545;
}

.btn-outline-danger:hover {
    background-color: #dc3545;
    color: white;
}

.btn-group {
    display: inline-flex;
    gap: 0.1875rem;
}

/* Table Responsive */
.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.fw-semibold {
    font-weight: 600;
    font-size: 0.875rem;
}

.text-primary {
    color: #007bff !important;
}

/* Custom Scrollbar */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: var(--light-bg);
}

::-webkit-scrollbar-thumb {
    background: var(--text-secondary);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: var(--text-primary);
}

</style>
@endpush

@section('content')
<div class="modern-dashboard">
    <!-- Header -->
    <div class="dashboard-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="dashboard-title">Today's Sales</h1>
                <p class="dashboard-subtitle">Sales Summary</p>
            </div>
            <div class="auto-refresh-indicator">
                <small class="text-muted">
                    <i class="fas fa-sync-alt me-1"></i>
                    Auto-refresh: <span id="refreshTimer">5:00</span>
                </small>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <!-- Total Sales -->
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon red">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
            <div class="stat-content">
            <div class="stat-value">
                @if($todayStats['revenue'] >= 1000)
                    TZS {{ number_format($todayStats['revenue'] / 1000, 1) }}k
                @else
                    TZS {{ number_format($todayStats['revenue'], 0) }}
                @endif
            </div>
            <div class="stat-label">Total Sales</div>
            <div class="stat-change {{ isset($yesterdayStats) && $yesterdayStats['revenue'] > 0 && $todayStats['revenue'] >= $yesterdayStats['revenue'] ? 'positive' : 'negative' }}">
                <i class="fas fa-arrow-{{ isset($yesterdayStats) && $yesterdayStats['revenue'] > 0 && $todayStats['revenue'] >= $yesterdayStats['revenue'] ? 'up' : 'down' }}"></i>
                @if(isset($yesterdayStats) && $yesterdayStats['revenue'] > 0)
                    {{ $yesterdayStats['revenue'] > 0 ? ($todayStats['revenue'] >= $yesterdayStats['revenue'] ? '+' : '') . number_format((($todayStats['revenue'] - $yesterdayStats['revenue']) / $yesterdayStats['revenue']) * 100, 1) : '0' }}% from yesterday
                @else
                    Today's sales
                @endif
            </div>
            </div>
        </div>

        <!-- Total Orders -->
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon blue">
                    <i class="fas fa-shopping-cart"></i>
                </div>
            </div>
            <div class="stat-content">
            <div class="stat-value">{{ $todayStats['orders'] }}</div>
            <div class="stat-label">Total Order</div>
            <div class="stat-change {{ isset($yesterdayStats) && $yesterdayStats['orders'] > 0 && $todayStats['orders'] >= $yesterdayStats['orders'] ? 'positive' : 'negative' }}">
                <i class="fas fa-arrow-{{ isset($yesterdayStats) && $yesterdayStats['orders'] > 0 && $todayStats['orders'] >= $yesterdayStats['orders'] ? 'up' : 'down' }}"></i>
                @if(isset($yesterdayStats) && $yesterdayStats['orders'] > 0)
                    {{ $yesterdayStats['orders'] > 0 ? ($todayStats['orders'] >= $yesterdayStats['orders'] ? '+' : '') . number_format((($todayStats['orders'] - $yesterdayStats['orders']) / $yesterdayStats['orders']) * 100, 1) : '0' }}% from yesterday
                @else
                    Today's orders
                @endif
            </div>
            </div>
        </div>

        <!-- Products Sold -->
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon green">
                    <i class="fas fa-box"></i>
                </div>
            </div>
            <div class="stat-content">
            <div class="stat-value">{{ $todayStats['approved'] }}</div>
            <div class="stat-label">Product Sold</div>
            <div class="stat-change {{ isset($yesterdayStats) && $yesterdayStats['approved'] > 0 && $todayStats['approved'] >= $yesterdayStats['approved'] ? 'positive' : 'negative' }}">
                <i class="fas fa-arrow-{{ isset($yesterdayStats) && $yesterdayStats['approved'] > 0 && $todayStats['approved'] >= $yesterdayStats['approved'] ? 'up' : 'down' }}"></i>
                @if(isset($yesterdayStats) && $yesterdayStats['approved'] > 0)
                    {{ $yesterdayStats['approved'] > 0 ? ($todayStats['approved'] >= $yesterdayStats['approved'] ? '+' : '') . number_format((($todayStats['approved'] - $yesterdayStats['approved']) / $yesterdayStats['approved']) * 100, 1) : '0' }}% from yesterday
                @else
                    Approved today
                @endif
            </div>
            </div>
        </div>

        <!-- New Customers -->
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon orange">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="stat-content">
            <div class="stat-value">{{ $stats['total_customers'] }}</div>
            <div class="stat-label">Total Customers</div>
            <div class="stat-change positive">
                <i class="fas fa-users"></i>
                All registered
            </div>
            </div>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="dashboard-grid">
        <!-- Monthly Orders Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <div>
                    <h3 class="chart-title">Monthly Orders</h3>
                    <p class="chart-subtitle">Orders from January to December</p>
                </div>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-primary active" id="currentYearBtn" onclick="switchYear('current')">
                        {{ $currentYear }}
                    </button>
                    <button type="button" class="btn btn-outline-primary" id="previousYearBtn" onclick="switchYear('previous')">
                        {{ $previousYear }}
                    </button>
                </div>
            </div>
            <div class="chart-container" style="position: relative; height: 300px;">
                <canvas id="monthlyOrdersChart"></canvas>
            </div>
        </div>

        <!-- Earnings Widget -->
        <div class="chart-card">
            <div class="chart-header">
                <div>
                    <h3 class="chart-title">Earnings</h3>
                    <p class="chart-subtitle">Total Expense</p>
                </div>
            </div>
            <div class="earnings-widget">
                <div class="earnings-circle">
                    <canvas id="earningsChart"></canvas>
                    <div class="earnings-value">
                        <div class="earnings-percentage">80%</div>
                    </div>
                </div>
                <div class="earnings-amount">TZS {{ number_format($todayStats['revenue'], 0) }}</div>
                <div class="earnings-subtitle">Profit is 48% More than last Month</div>
            </div>
        </div>
    </div>

    <!-- Recent Orders Table -->
    <div class="chart-card" style="margin-top: 1rem;">
        <div class="chart-header">
            <div>
                <h3 class="chart-title">Recent Orders</h3>
                <p class="chart-subtitle">Latest customer orders</p>
            </div>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-eye me-1"></i>View All
            </a>
        </div>
        <div class="table-responsive">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Order Code</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $order)
                        <tr>
                            <td data-label="Order">
                                <strong class="text-primary">{{ $order->order_code }}</strong>
                            </td>
                            <td data-label="Customer">
                                <div>
                                    <div class="fw-semibold">{{ $order->user->name }}</div>
                                    <small class="text-muted">{{ $order->user->phone }}</small>
                                </div>
                            </td>
                            <td data-label="Date">
                                <div>{{ $order->created_at->format('M d, Y') }}</div>
                                <small class="text-muted">{{ $order->created_at->format('h:i A') }}</small>
                            </td>
                            <td data-label="Total">
                                <strong>TZS {{ number_format($order->total_amount, 0) }}</strong>
                            </td>
                            <td data-label="Status">
                                @if($order->approval_status === 'approved')
                                    <span class="badge bg-success">Approved</span>
                                @elseif($order->approval_status === 'requested')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @elseif($order->approval_status === 'cancelled')
                                    <span class="badge bg-danger">Cancelled</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($order->approval_status) }}</span>
                                @endif
                            </td>
                            <td data-label="Actions">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('admin.orders.show', $order->order_code) }}" 
                                       class="btn btn-outline-primary" 
                                       title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($order->approval_status === 'requested')
                                        <form method="POST" action="{{ route('admin.orders.approve', $order->order_code) }}" class="d-inline" id="approveForm{{ $order->id }}">
                                            @csrf
                                            <button type="button" 
                                                    class="btn btn-outline-success" 
                                                    title="Approve Order"
                                                    onclick="modernConfirm('Approve this order? This will reduce product stock.', () => document.getElementById('approveForm{{ $order->id }}').submit(), { title: 'Approve Order', type: 'success', icon: 'fa-check-circle', confirmText: 'Approve' })">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.orders.cancel', $order->order_code) }}" class="d-inline" id="cancelForm{{ $order->id }}">
                                            @csrf
                                            <button type="button" 
                                                    class="btn btn-outline-danger" 
                                                    title="Cancel Order"
                                                    onclick="modernConfirm('Cancel this order? This action cannot be undone.', () => document.getElementById('cancelForm{{ $order->id }}').submit(), { title: 'Cancel Order', type: 'danger', icon: 'fa-times-circle', confirmText: 'Cancel Order' })">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <i class="fas fa-inbox fa-2x text-muted mb-2 d-block"></i>
                                <p class="text-muted mb-0">No recent orders found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top Products Section -->
    <div class="chart-card" style="margin-top: 1rem;">
        <div class="chart-header">
            <div>
                <h3 class="chart-title">Top Products</h3>
                <p class="chart-subtitle">Most popular products by sales</p>
            </div>
        </div>
        <div class="modern-table-container">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Popularity</th>
                        <th>Sales</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $topProducts = [
                            ['name' => 'Business Cards', 'popularity' => 86, 'sales' => '46%', 'color' => '#fd7e14'],
                            ['name' => 'Branded T-Shirts', 'popularity' => 67, 'sales' => '17%', 'color' => '#007bff'],
                            ['name' => 'Promotional Banners', 'popularity' => 45, 'sales' => '10%', 'color' => '#28a745'],
                            ['name' => 'Custom Mugs', 'popularity' => 38, 'sales' => '29%', 'color' => '#6f42c1'],
                        ];
                    @endphp
                    @foreach($topProducts as $index => $product)
                        <tr>
                            <td class="product-number">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                            <td class="product-name">{{ $product['name'] }}</td>
                            <td>
                                <div class="progress-bar-container">
                                    <div class="progress-bar-fill" style="width: {{ $product['popularity'] }}%; background: {{ $product['color'] }};"></div>
                                </div>
                            </td>
                            <td class="product-sales">{{ $product['sales'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Monthly Orders Line Chart
    const monthlyOrdersCtx = document.getElementById('monthlyOrdersChart');
    let monthlyOrdersChart = null;
    
    if (monthlyOrdersCtx) {
        // Real data from database for current year
        const currentYearData = {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Orders',
                data: @json($monthlyOrdersCurrentYear),
                borderColor: '#FF0000',
                backgroundColor: 'rgba(255, 0, 0, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#FF0000',
                pointBorderColor: '#FFFFFF',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8
            }]
        };

        // Real data from database for previous year
        const previousYearData = {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Orders',
                data: @json($monthlyOrdersPreviousYear),
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#007bff',
                pointBorderColor: '#FFFFFF',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8
            }]
        };

        monthlyOrdersChart = new Chart(monthlyOrdersCtx, {
            type: 'line',
            data: currentYearData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#FFFFFF',
                        bodyColor: '#FFFFFF',
                        borderColor: '#FF0000',
                        borderWidth: 1,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            title: function(context) {
                                return context[0].label + ' {{ $currentYear }}';
                            },
                            label: function(context) {
                                return 'Orders: ' + context.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#6c757d',
                            font: {
                                size: 12,
                                weight: '500'
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)',
                            drawBorder: false
                        },
                        ticks: {
                            color: '#6c757d',
                            font: {
                                size: 12,
                                weight: '500'
                            },
                            callback: function(value) {
                                return value;
                            }
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                elements: {
                    point: {
                        hoverBackgroundColor: '#FF0000'
                    }
                }
            }
        });
    }

    // Year switching function
    window.switchYear = function(year) {
        if (!monthlyOrdersChart) return;
        
        const currentBtn = document.getElementById('currentYearBtn');
        const previousBtn = document.getElementById('previousYearBtn');
        
        if (year === 'current') {
            currentBtn.classList.add('active');
            previousBtn.classList.remove('active');
            monthlyOrdersChart.data = {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Orders',
                    data: @json($monthlyOrdersCurrentYear),
                    borderColor: '#FF0000',
                    backgroundColor: 'rgba(255, 0, 0, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#FF0000',
                    pointBorderColor: '#FFFFFF',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            };
        } else {
            previousBtn.classList.add('active');
            currentBtn.classList.remove('active');
            monthlyOrdersChart.data = {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Orders',
                    data: @json($monthlyOrdersPreviousYear),
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#007bff',
                    pointBorderColor: '#FFFFFF',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            };
        }
        
        monthlyOrdersChart.update('active');
    };

    // Earnings Doughnut Chart
    const earningsCtx = document.getElementById('earningsChart');
    if (earningsCtx) {
        new Chart(earningsCtx, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [80, 20],
                    backgroundColor: ['#28a745', '#e9ecef'],
                    borderWidth: 0
                }]
            },
            options: {
                cutout: '80%',
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false }
                },
                responsive: true,
                maintainAspectRatio: true
            }
        });
    }

    // Animate stats on load
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });

    // Auto-refresh data every 5 minutes
    let refreshInterval = 300000; // 5 minutes in milliseconds
    let timeLeft = refreshInterval / 1000; // Convert to seconds
    
    setInterval(function() {
        refreshDashboardData();
        timeLeft = refreshInterval / 1000; // Reset timer
    }, refreshInterval);

    // Countdown timer
    setInterval(function() {
        timeLeft--;
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        const timerElement = document.getElementById('refreshTimer');
        if (timerElement) {
            timerElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        }
    }, 1000);

    // Function to refresh dashboard data
    function refreshDashboardData() {
        // Refresh monthly orders data for current year
        fetch('{{ route("admin.dashboard.monthly-orders") }}?year={{ $currentYear }}')
            .then(response => response.json())
            .then(data => {
                if (data.success && monthlyOrdersChart) {
                    monthlyOrdersChart.data.datasets[0].data = data.data;
                    monthlyOrdersChart.update('none'); // Update without animation
                    
                    // Show refresh notification
                    showSuccessToast('Dashboard data refreshed successfully');
                }
            })
            .catch(error => {
                console.log('Error refreshing dashboard data:', error);
            });
    }
});
</script>
@endpush
