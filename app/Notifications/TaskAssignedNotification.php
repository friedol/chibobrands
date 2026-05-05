<?php

namespace App\Notifications;

use App\Channels\CustomDatabaseChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssignedNotification extends Notification
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
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Design Task Assigned: ' . $this->task->title)
            ->line('A new design task has been assigned to you.')
            ->line('Task: ' . $this->task->title)
            ->line('Customer: ' . ($this->task->customer->name ?? 'N/A'))
            ->line('Priority: ' . $this->task->priority_label)
            ->line('Deadline: ' . ($this->task->deadline ? $this->task->deadline->format('M d, Y h:i A') : 'N/A'))
            ->action('View Task', route('admin.design-tasks.show', $this->task))
            ->line('Please check the task instructions and start working on it.');
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
            'title' => 'New Task Assigned: ' . $this->task->title,
            'message' => 'You have been assigned a new design task for ' . ($this->task->customer->name ?? 'a customer') . '.',
            'type' => 'task_assigned',
            'priority' => $this->task->priority,
            'customer_name' => $this->task->customer->name ?? 'N/A',
            'deadline' => $this->task->deadline,
            'url' => route('admin.design-tasks.show', $this->task),
            'related_id' => $this->task->id,
            'related_type' => 'App\\Models\\DesignTask',
        ];
    }
}
