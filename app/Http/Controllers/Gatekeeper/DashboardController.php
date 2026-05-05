<?php

namespace App\Http\Controllers\Gatekeeper;

use App\Http\Controllers\Controller;
use App\Models\ProductMovement;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'today');
        
        // Define date range based on period
        $dateRange = match($period) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            '6_months' => [now()->subMonths(6), now()->endOfDay()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            '2_years' => [now()->subYears(2), now()->endOfDay()],
            'custom' => [
                $request->get('start_date') ? Carbon::parse($request->get('start_date'))->startOfDay() : null,
                $request->get('end_date') ? Carbon::parse($request->get('end_date'))->endOfDay() : null
            ],
            'all' => [null, null],
            default => [now()->startOfMonth(), now()->endOfMonth()]
        };

        // Helper to apply range
        $applyDate = function($query) use ($dateRange) {
            if ($dateRange[0] && $dateRange[1]) {
                $query->whereBetween('movement_date', [$dateRange[0], $dateRange[1]]);
            }
            return $query;
        };

        // 1. Stats Cards Data
        $stats = [
            'total_movements' => $applyDate(ProductMovement::query())->count(),
            'total_in' => $applyDate(ProductMovement::where('type', 'in'))->count(),
            'total_out' => $applyDate(ProductMovement::where('type', 'out'))->count(),
            'my_entries_count' => $applyDate(ProductMovement::where('gatekeeper_id', Auth::id()))->count(),
        ];

        // 2. Recent Movements (Filtered by Period)
        $recentMovements = $applyDate(ProductMovement::with('gatekeeper'))
                            ->latest('movement_date')
                            ->take(10)
                            ->get();

        // 3. My Recent Activity
        $myRecentActivity = ProductMovement::where('gatekeeper_id', Auth::id())
                            ->latest('created_at')
                            ->take(6)
                            ->get();

        // 4. Chart Data: Trend (Over time)
        // Grouping logic
        $labels = [];
        $dataIn = [];
        $dataOut = [];

        if ($period == 'today') {
            // Hourly breakdown
            for ($i = 0; $i <= 23; $i+=2) { // Every 2 hours
                $start = now()->startOfDay()->addHours($i);
                $end = $start->copy()->addHours(2)->subSecond();
                
                $labels[] = $start->format('H:00');
                $dataIn[] = ProductMovement::whereBetween('movement_date', [$start, $end])->where('type', 'in')->count();
                $dataOut[] = ProductMovement::whereBetween('movement_date', [$start, $end])->where('type', 'out')->count();
            }
        } elseif ($period == 'week' || $period == 'month') {
            // Daily breakdown
            $days = $period == 'week' ? 7 : 30;
            // Loop backwards from today or start from range? Better to go chronological for chart
            $start = $dateRange[0]->copy();
            $end = $dateRange[1]->copy();
            
            // Adjust loop to go day by day
            $current = $start->copy();
            while ($current <= $end && $current <= now()) {
                $labels[] = $current->format('M d');
                $dataIn[] = ProductMovement::whereDate('movement_date', $current)->where('type', 'in')->count();
                $dataOut[] = ProductMovement::whereDate('movement_date', $current)->where('type', 'out')->count();
                $current->addDay();
            }
        } else {
            // Monthly breakdown
            $start = $dateRange[0]->copy();
            // Round start to start of month
            $start->startOfMonth();
            $end = now();
            
            $current = $start->copy();
            while ($current <= $end) {
                $labels[] = $current->format('M Y');
                $dataIn[] = ProductMovement::whereYear('movement_date', $current->year)
                                            ->whereMonth('movement_date', $current->month)
                                            ->where('type', 'in')->count();
                $dataOut[] = ProductMovement::whereYear('movement_date', $current->year)
                                            ->whereMonth('movement_date', $current->month)
                                            ->where('type', 'out')->count();
                $current->addMonth();
            }
        }

        $trendChart = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Incoming',
                    'data' => $dataIn,
                    'borderColor' => '#1cc88a', // Success Green
                    'backgroundColor' => 'rgba(28, 200, 138, 0.05)',
                ],
                [
                    'label' => 'Outgoing',
                    'data' => $dataOut,
                    'borderColor' => '#f6c23e', // Warning Yellow
                    'backgroundColor' => 'rgba(246, 194, 62, 0.05)',
                ]
            ]
        ];

        // 5. Chart Data: Distribution (Pie)
        $distributionChart = [
            'labels' => ['Incoming', 'Outgoing'],
            'data' => [$stats['total_in'], $stats['total_out']],
            'colors' => ['#1cc88a', '#f6c23e']
        ];

        // 6. Ready for Pickup Tasks (DesignTasks)
        $readyForPickupTasks = \App\Models\DesignTask::query()
            ->with(['customer', 'receptionist'])
            ->whereNotNull('delivery_status')
            ->where('delivery_status', '=', 'ready_for_pickup')
            ->latest('updated_at')
            ->take(10)
            ->get();

        return view('gatekeeper.dashboard', compact('stats', 'recentMovements', 'myRecentActivity', 'trendChart', 'distributionChart', 'period', 'readyForPickupTasks'));
    }
}
