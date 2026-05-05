<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use App\Models\Notification as NotificationModel;

class CustomDatabaseChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        $data = $notification->toArray($notifiable);

        return NotificationModel::create([
            'user_id' => $notifiable->id,
            'sender_id' => $data['sender_id'] ?? (auth()->id() ?? null),
            'type' => $data['type'] ?? 'general',
            'message' => $data['message'] ?? '',
            'status' => 'unread',
            'related_id' => $data['related_id'] ?? null,
            'related_type' => $data['related_type'] ?? null,
        ]);
    }
}
