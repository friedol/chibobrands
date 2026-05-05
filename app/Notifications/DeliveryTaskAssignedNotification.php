<?php

namespace App\Notifications;

use App\Channels\CustomDatabaseChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DeliveryTaskAssignedNotification extends Notification
{
    use Queueable;

    protected $task;

    /**
     * Create a new notification instance.
     */
    public function __construct($task)
    {
        $this->task = $task;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [CustomDatabaseChannel::class];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'title' => 'New Delivery Task: ' . $this->task->task_code,
            'message' => 'You have been assigned a new delivery task for ' . ($this->task->customer->name ?? 'a customer') . '.',
            'type' => 'delivery_assigned',
            'priority' => $this->task->priority,
            'customer_name' => $this->task->customer->name ?? 'N/A',
            'url' => route('admin.design-tasks.show', $this->task),
            'related_id' => $this->task->id,
            'related_type' => 'App\\Models\\DesignTask',
        ];
    }
}
