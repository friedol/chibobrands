<?php

namespace App\Http\Controllers;

use App\Models\DesignTask;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DeliveryPerformanceController extends Controller
{
    public function index(Request $request)
    {
        // Check if custom date range is provided
        $customDateFrom = $request->input('date_from');
        $customDateTo = $request->input('date_to');
        $period = $request->input('period', 'month');
        $deliveryPersonId = $request->input('delivery_person_id');

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
                    $dateFrom = DesignTask::min('created_at') ?? now()->subYear();
                    $dateTo = now();
                    break;
                default:
                    $dateFrom = now()->startOfMonth();
                    $dateTo = now()->endOfMonth();
                    break;
            }
        }

        // Get all delivery personnel
        $allDeliveryPersonnel = User::where('role', 'delivery')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Base query for design tasks with delivery assignments
        $query = DesignTask::whereNotNull('delivery_id')
            ->whereBetween('created_at', [$dateFrom, $dateTo]);

        // Filter by specific delivery person if selected
        if ($deliveryPersonId) {
            $query->where('delivery_id', $deliveryPersonId);
        }

        // Summary statistics
        $summary = [
            'total_deliveries' => $query->count(),
            'delivered' => (clone $query)->where('delivery_status', 'delivered')->count(),
            'pending' => (clone $query)->where('delivery_status', 'pending')->count(),
            'active_personnel' => $allDeliveryPersonnel->count(),
        ];

        // Delivery personnel performance rankings
        $deliveryPersonnel = User::where('role', 'delivery')
            ->where('is_active', true)
            ->withCount([
                'deliveryTasks as total_tasks' => function ($q) use ($dateFrom, $dateTo) {
                    $q->whereBetween('created_at', [$dateFrom, $dateTo]);
                },
                'deliveryTasks as delivered_count' => function ($q) use ($dateFrom, $dateTo) {
                    $q->where('delivery_status', 'delivered')
                      ->whereBetween('created_at', [$dateFrom, $dateTo]);
                },
                'deliveryTasks as pending_count' => function ($q) use ($dateFrom, $dateTo) {
                    $q->where('delivery_status', 'pending')
                      ->whereBetween('created_at', [$dateFrom, $dateTo]);
                },
            ])
            ->having('total_tasks', '>', 0)
            ->orderByDesc('delivered_count')
            ->get();

        // Trend data (last 30 days) - Fill all dates even if no data
        $startDate = now()->subDays(30);
        $endDate = now();
        
        // Get actual delivery data
        $deliveryData = DesignTask::selectRaw('DATE(delivered_at) as date, COUNT(*) as count')
            ->whereNotNull('delivered_at')
            ->whereBetween('delivered_at', [$startDate, $endDate])
            ->when($deliveryPersonId, function ($q) use ($deliveryPersonId) {
                $q->where('delivery_id', $deliveryPersonId);
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
                'count' => $deliveryData[$dateKey] ?? 0
            ]);
            $currentDate->addDay();
        }

        // Status distribution
        $statusDistribution = [
            'Delivered' => (clone $query)->where('delivery_status', 'delivered')->count(),
            'Pending' => (clone $query)->where('delivery_status', 'pending')->count(),
            'Failed' => (clone $query)->where('delivery_status', 'failed')->count(),
        ];
        
        // Recent deliveries for report
        $recentDeliveries = (clone $query)->with(['customer', 'delivery'])
            ->latest('delivered_at')
            ->take(50)
            ->get();

        return view('admin.reports.delivery-performance', compact(
            'summary',
            'deliveryPersonnel',
            'allDeliveryPersonnel',
            'trendData',
            'statusDistribution',
            'period',
            'deliveryPersonId',
            'recentDeliveries'
        ));
    }

    public function export(Request $request)
    {
        // PDF export logic similar to SalerPerformanceController
        return response()->json(['message' => 'Export functionality coming soon']);
    }
}
