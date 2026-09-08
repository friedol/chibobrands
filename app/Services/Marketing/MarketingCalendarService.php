<?php

namespace App\Services\Marketing;

use App\Models\MarketingCalendarEvent;

class MarketingCalendarService
{
    /**
     * Create a calendar event for a marketing entity.
     */
    public function createEventFor($source, $title, $startDate, $endDate, $details = [])
    {
        return MarketingCalendarEvent::create([
            'source_type' => get_class($source),
            'source_id' => $source->id,
            'title' => $title,
            'description' => $details['description'] ?? null,
            'start_datetime' => $startDate,
            'end_datetime' => $endDate,
            'platform' => $details['platform'] ?? null,
            'content_format' => $details['content_format'] ?? null,
            'objective' => $details['objective'] ?? null,
            'segment' => $details['segment'] ?? null,
            'assigned_to' => $details['assigned_to'] ?? null,
            'status' => $details['status'] ?? 'scheduled',
        ]);
    }

    /**
     * Delete calendar events for a given source.
     */
    public function deleteEventsFor($source)
    {
        return MarketingCalendarEvent::where('source_type', get_class($source))
            ->where('source_id', $source->id)
            ->delete();
    }
}
