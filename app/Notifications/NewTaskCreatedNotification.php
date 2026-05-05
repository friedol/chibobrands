<?php

namespace App\Notifications;

use App\Channels\CustomDatabaseChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewTaskCreatedNotification extends Notification
{
    use Queueable;

    protected $task;
    protected $creator;

    /**
     * Create a new notification instance.
     */
    public function __construct($task, $creator)
    {
        $this->task = $task;
        $this->creator = $creator;
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
            ->subject('New Design Task Created: ' . $this->task->title)
            ->line('A new design task has been created by ' . $this->creator->name)
            ->line('Task: ' . $this->task->title)
            ->line('Customer: ' . ($this->task->customer->name ?? 'N/A'))
            ->line('Priority: ' . $this->task->priority_label)
            ->action('Process Task', route('admin.design-tasks.show', $this->task))
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
            'title' => 'New Task Created: ' . $this->task->title,
            'message' => "A new design task has been created by {$this->creator->name} for " . ($this->task->customer->name ?? 'a customer') . ".",
            'type' => 'task_created',
            'creator_name' => $this->creator->name,
            'priority' => $this->task->priority,
            'url' => route('admin.design-tasks.show', $this->task),
            'related_id' => $this->task->id,
            'related_type' => 'App\\Models\\DesignTask',
        ];
    }
}
