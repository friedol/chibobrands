<?php

namespace App\Notifications;

use App\Channels\CustomDatabaseChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskStatusUpdatedNotification extends Notification
{
    use Queueable;

    protected $task;
    protected $updater;
    protected $oldStatus;

    /**
     * Create a new notification instance.
     */
    public function __construct($task, $updater, $oldStatus)
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
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Design Task Status Updated: ' . $this->task->title)
            ->line('A design task status has been updated.')
            ->line('Task: ' . $this->task->title)
            ->line('From: ' . ucfirst($this->oldStatus))
            ->line('To: ' . ucfirst($this->task->status))
            ->line('Updated By: ' . $this->updater->name);

        if ($this->task->status === \App\Models\DesignTask::STATUS_SUPER_COMPLETED && $this->task->pickup_code) {
            $mail->line('Verification Code: ' . $this->task->pickup_code);
        }

        return $mail->action('View Task', route('admin.design-tasks.show', $this->task))
            ->line('Thank you for using our application!');
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
            'title' => 'Task Status Updated: ' . $this->task->title,
            'message' => "Status changed from {$this->oldStatus} to {$this->task->status} by {$this->updater->name}.",
            'type' => 'task_status_updated',
            'old_status' => $this->oldStatus,
            'new_status' => $this->task->status,
            'updater_name' => $this->updater->name,
            'pickup_code' => ($this->task->status === \App\Models\DesignTask::STATUS_SUPER_COMPLETED) ? $this->task->pickup_code : null,
            'url' => route('admin.design-tasks.show', $this->task),
            'related_id' => $this->task->id,
            'related_type' => 'App\\Models\\DesignTask',
        ];
    }
}
