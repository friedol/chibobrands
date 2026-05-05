<?php

namespace App\Notifications;

use App\Channels\CustomDatabaseChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DeliveryTaskUpdatedNotification extends Notification
{
    use Queueable;

    protected $task;
    protected $updater;
    protected $oldStatus;

    /**
     * Create a new notification instance.
     */
    public function __construct($task, $updater, $oldStatus = null)
    {
        $this->task = $task;
        $this->updater = $updater;
        $this->oldStatus = $oldStatus;
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
        $status = ucfirst($this->task->delivery_status);
        return [
            'task_id' => $this->task->id,
            'title' => 'Delivery Update: ' . $this->task->task_code,
            'message' => "Delivery status for task #{$this->task->task_code} updated to {$status} by {$this->updater->name}.",
            'type' => 'delivery_updated',
            'delivery_status' => $this->task->delivery_status,
            'url' => route('admin.design-tasks.show', $this->task),
            'related_id' => $this->task->id,
            'related_type' => 'App\\Models\\DesignTask',
        ];
    }
}
