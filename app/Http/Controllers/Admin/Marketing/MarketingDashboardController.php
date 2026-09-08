<?php

namespace App\Http\Controllers\Admin\Marketing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Campaign;
use App\Models\Ad;
use App\Models\MarketingCalendarEvent;

class MarketingDashboardController extends Controller
{
    public function index()
    {
        $activeCampaigns = Campaign::where('status', 'active')->count();
        $runningAds = Ad::where('status', 'active')->count();
        $upcomingEvents = MarketingCalendarEvent::where('start_datetime', '>=', now())
                            ->orderBy('start_datetime')
                            ->take(5)
                            ->get();

        // Real data calculations
        // We'll use AdPerformanceReport for actual metrics. If none exist, they will gracefully default to 0.
        $totalReach = \App\Models\AdPerformanceReport::sum('impressions');
        $totalConversions = \App\Models\AdPerformanceReport::sum('conversions');
        $totalSpend = \App\Models\AdPerformanceReport::sum('amount_spent');
        
        $cpc = $totalConversions > 0 ? ($totalSpend / $totalConversions) : 0; // Cost Per Conversion

        $stats = [
            'active_campaigns' => $activeCampaigns,
            'running_ads' => $runningAds,
            'total_reach' => $totalReach,
            'total_conversions' => $totalConversions,
            'marketing_spend' => $totalSpend,
            'roi_percentage' => number_format($cpc, 2), // We display CPC instead since ROI needs revenue tracking
        ];

        // 6-Month Trend Chart Data (Real)
        $sixMonthsAgo = now()->subMonths(5)->startOfMonth(); // 6 months including current
        
        $monthlyReports = \App\Models\AdPerformanceReport::selectRaw('
            YEAR(report_date) as year, 
            MONTH(report_date) as month, 
            SUM(impressions) as reach, 
            SUM(conversions) as conversions,
            SUM(amount_spent) as spend
        ')
        ->where('report_date', '>=', $sixMonthsAgo)
        ->groupBy('year', 'month')
        ->orderBy('year')
        ->orderBy('month')
        ->get();

        $months = [];
        $campaign_reach = [];
        $conversions = [];
        $ad_spend = [];

        // Pre-fill the last 6 months with 0s to ensure the chart always looks right even with no data
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $key = $date->format('Y-n');
            $months[$key] = $date->format('M');
            $campaign_reach[$key] = 0;
            $conversions[$key] = 0;
            $ad_spend[$key] = 0;
        }

        foreach ($monthlyReports as $report) {
            $key = $report->year . '-' . $report->month;
            if (isset($months[$key])) {
                $campaign_reach[$key] = $report->reach;
                $conversions[$key] = $report->conversions;
                $ad_spend[$key] = $report->spend;
            }
        }

        // Platform Distribution (Real)
        $platformStats = Ad::selectRaw('platform, COUNT(*) as count')
            ->groupBy('platform')
            ->get();
            
        $platforms = [];
        $platform_shares = [];
        
        if ($platformStats->isEmpty()) {
            // Provide empty default if no ads exist so the chart doesn't crash
            $platforms = ['No Data'];
            $platform_shares = [1];
        } else {
            foreach ($platformStats as $stat) {
                $platforms[] = ucfirst($stat->platform);
                $platform_shares[] = $stat->count;
            }
        }

        $chartData = [
            'months' => array_values($months),
            'campaign_reach' => array_values($campaign_reach),
            'ad_spend' => array_values($ad_spend),
            'conversions' => array_values($conversions),
            'platforms' => $platforms,
            'platform_shares' => $platform_shares
        ];

        return view('admin.marketing.dashboard', compact('activeCampaigns', 'runningAds', 'upcomingEvents', 'stats', 'chartData'));
    }
}
