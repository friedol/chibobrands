<?php

namespace App\Http\Controllers;

use App\Models\ProductMovement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GatekeeperPerformanceController extends Controller
{
    public function index(Request $request)
    {
        // Check if custom date range is provided
        $customDateFrom = $request->input('date_from');
        $customDateTo = $request->input('date_to');
        $period = $request->input('period', 'month');
        $gatekeeperId = $request->input('gatekeeper_id');

        // Use custom dates if provided, otherwise use period
        if ($customDateFrom && $customDateTo) {
            $dateFrom = Carbon::parse($customDateFrom)->startOfDay();
            $dateTo = Carbon::parse($customDateTo)->endOfDay();
            $period = ''; // Clear period when using custom dates
        } else {
            // Calculate date range based on period
            switch ($period) {
                case 'today':
                    $dateFrom = now()->startOfDay();
                    $dateTo = now()->endOfDay();
                    break;
                case 'week':
                    $dateFrom = now()->startOfWeek();
                    $dateTo = now()->endOfWeek();
                    break;
                case 'month':
                    $dateFrom = now()->startOfMonth();
                    $dateTo = now()->endOfMonth();
                    break;
                case 'quarter':
                    $dateFrom = now()->startOfQuarter();
                    $dateTo = now()->endOfQuarter();
                    break;
                case 'half_year':
                    $dateFrom = now()->subMonths(6)->startOfDay();
                    $dateTo = now()->endOfDay();
                    break;
                case 'year':
                    $dateFrom = now()->startOfYear();
                    $dateTo = now()->endOfYear();
                    break;
                case 'all':
                    $dateFrom = ProductMovement::min('movement_date') ?? now()->subYear();
                    $dateTo = now();
                    break;
                default:
                    $dateFrom = now()->startOfMonth();
                    $dateTo = now()->endOfMonth();
                    break;
            }
        }

        // Get all gatekeepers
        $allGatekeepers = User::where('role', 'gatekeeper')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Base query
        $query = ProductMovement::whereBetween('movement_date', [$dateFrom, $dateTo]);

        // Filter by specific gatekeeper if selected
        if ($gatekeeperId) {
            $query->where('gatekeeper_id', $gatekeeperId);
        }

        // Summary statistics
        $summary = [
            'total_movements' => $query->count(),
            'total_in' => (clone $query)->where('type', 'in')->count(),
            'total_out' => (clone $query)->where('type', 'out')->count(),
            'active_gatekeepers' => $allGatekeepers->count(),
        ];

        // Gatekeeper performance rankings
        $gatekeepers = User::where('role', 'gatekeeper')
            ->where('is_active', true)
            ->withCount([
                'gatekeeperMovements as total_movements' => function ($q) use ($dateFrom, $dateTo) {
                    $q->whereBetween('movement_date', [$dateFrom, $dateTo]);
                },
                'gatekeeperMovements as movements_in' => function ($q) use ($dateFrom, $dateTo) {
                    $q->where('type', 'in')->whereBetween('movement_date', [$dateFrom, $dateTo]);
                },
                'gatekeeperMovements as movements_out' => function ($q) use ($dateFrom, $dateTo) {
                    $q->where('type', 'out')->whereBetween('movement_date', [$dateFrom, $dateTo]);
                },
            ])
            ->having('total_movements', '>', 0)
            ->orderByDesc('total_movements')
            ->get();

        // Trend data (last 30 days) - Fill all dates even if no data
        $startDate = now()->subDays(30);
        $endDate = now();
        
        // Get actual movement data
        $movementData = ProductMovement::selectRaw('DATE(movement_date) as date, COUNT(*) as count')
            ->whereBetween('movement_date', [$startDate, $endDate])
            ->when($gatekeeperId, function ($q) use ($gatekeeperId) {
                $q->where('gatekeeper_id', $gatekeeperId);
            })
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        // Create complete 30-day dataset
        $trendData = collect();
        $currentDate = $startDate->copy();
        
        while ($currentDate <= $endDate) {
            $dateKey = $currentDate->format('Y-m-d');
            $trendData->push([
                'date' => $currentDate->format('M d'),
                'count' => $movementData[$dateKey] ?? 0
            ]);
            $currentDate->addDay();
        }

        // Type distribution
        $typeDistribution = [
            'Incoming' => (clone $query)->where('type', 'in')->count(),
            'Outgoing' => (clone $query)->where('type', 'out')->count(),
        ];

        // Recent movements (last 10)
        $recentMovements = ProductMovement::with(['gatekeeper', 'product'])
            ->whereBetween('movement_date', [$dateFrom, $dateTo])
            ->when($gatekeeperId, function ($q) use ($gatekeeperId) {
                $q->where('gatekeeper_id', $gatekeeperId);
            })
            ->orderBy('movement_date', 'desc')
            ->limit(50)
            ->get();

        return view('admin.reports.gatekeeper-performance', compact(
            'summary',
            'gatekeepers',
            'allGatekeepers',
            'trendData',
            'typeDistribution',
            'recentMovements',
            'period',
            'gatekeeperId'
        ));
    }

    public function export(Request $request)
    {
        // Get the same data as index
        $customDateFrom = $request->input('date_from');
        $customDateTo = $request->input('date_to');
        $period = $request->input('period', 'month');
        $gatekeeperId = $request->input('gatekeeper_id');

        // Use custom dates if provided, otherwise use period
        if ($customDateFrom && $customDateTo) {
            $dateFrom = Carbon::parse($customDateFrom)->startOfDay();
            $dateTo = Carbon::parse($customDateTo)->endOfDay();
        } else {
            switch ($period) {
                case 'today':
                    $dateFrom = now()->startOfDay();
                    $dateTo = now()->endOfDay();
                    break;
                case 'week':
                    $dateFrom = now()->startOfWeek();
                    $dateTo = now()->endOfWeek();
                    break;
                case 'month':
                    $dateFrom = now()->startOfMonth();
                    $dateTo = now()->endOfMonth();
                    break;
                case 'quarter':
                    $dateFrom = now()->startOfQuarter();
                    $dateTo = now()->endOfQuarter();
                    break;
                case 'half_year':
                    $dateFrom = now()->subMonths(6)->startOfDay();
                    $dateTo = now()->endOfDay();
                    break;
                case 'year':
                    $dateFrom = now()->startOfYear();
                    $dateTo = now()->endOfYear();
                    break;
                case 'all':
                    $dateFrom = ProductMovement::min('movement_date') ?? now()->subYear();
                    $dateTo = now();
                    break;
                default:
                    $dateFrom = now()->startOfMonth();
                    $dateTo = now()->endOfMonth();
                    break;
            }
        }

        $allGatekeepers = User::where('role', 'gatekeeper')->where('is_active', true)->orderBy('name')->get();
        $query = ProductMovement::whereBetween('movement_date', [$dateFrom, $dateTo]);
        if ($gatekeeperId) $query->where('gatekeeper_id', $gatekeeperId);

        $summary = [
            'total_movements' => $query->count(),
            'total_in' => (clone $query)->where('type', 'in')->count(),
            'total_out' => (clone $query)->where('type', 'out')->count(),
            'active_gatekeepers' => $allGatekeepers->count(),
        ];

        $gatekeepers = User::where('role', 'gatekeeper')->where('is_active', true)
            ->withCount([
                'gatekeeperMovements as total_movements' => fn($q) => $q->whereBetween('movement_date', [$dateFrom, $dateTo]),
                'gatekeeperMovements as movements_in' => fn($q) => $q->where('type', 'in')->whereBetween('movement_date', [$dateFrom, $dateTo]),
                'gatekeeperMovements as movements_out' => fn($q) => $q->where('type', 'out')->whereBetween('movement_date', [$dateFrom, $dateTo]),
            ])
            ->having('total_movements', '>', 0)
            ->orderByDesc('total_movements')
            ->get();

        $recentMovements = ProductMovement::with(['gatekeeper', 'product'])
            ->whereBetween('movement_date', [$dateFrom, $dateTo])
            ->when($gatekeeperId, fn($q) => $q->where('gatekeeper_id', $gatekeeperId))
            ->orderBy('movement_date', 'desc')
            ->limit(50)
            ->get();

        $pdf = \PDF::loadView('admin.reports.gatekeeper-performance-pdf', compact(
            'summary', 'gatekeepers', 'recentMovements', 'period', 'dateFrom', 'dateTo'
        ));

        return $pdf->download('gatekeeper-performance-' . now()->format('Y-m-d') . '.pdf');
    }
}
