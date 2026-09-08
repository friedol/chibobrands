<?php

namespace App\Services\Marketing;

use App\Models\Campaign;

class CampaignAnalyticsService
{
    /**
     * Get analytics for a campaign.
     */
    public function getAnalyticsFor(Campaign $campaign)
    {
        // Calculate ROI, total reach, engagement across all ads and activities
        $totalSpent = $campaign->ads()->sum('budget'); // Or sum from performance reports
        $totalReach = $campaign->ads()->with('performanceReports')->get()->sum(function($ad) {
            return $ad->performanceReports->sum('impressions');
        });
        
        return [
            'total_spent' => $totalSpent,
            'total_reach' => $totalReach,
            // Add more metrics here
        ];
    }
}
