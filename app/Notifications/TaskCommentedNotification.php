<?php

namespace App\Notifications;

use App\Channels\CustomDatabaseChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskCommentedNotification extends Notification
{
    use Queueable;

    protected $task;
    protected $commenter;
    protected $comment;

    /**
     * Create a new notification instance.
     */
    public function __construct($task, $commenter, $comment)
    {
        $this->task = $task;
        $this->commenter = $commenter;
        $this->comment = $comment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [CustomDatabaseChannel::class, 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Comment on Task: ' . $this->task->title)
            ->line('A new comment has been added to your design task by ' . $this->commenter->name)
            ->line('Task: ' . $this->task->title)
            ->line('Comment: ' . str()->limit($this->comment, 100))
            ->action('View Task', route('admin.design-tasks.show', $this->task))
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
            'title' => 'New Comment: ' . $this->task->title,
            'message' => "{$this->commenter->name} added a comment: " . str()->limit($this->comment, 50),
            'type' => 'task_commented',
            'commenter_name' => $this->commenter->name,
            'url' => route('admin.design-tasks.show', $this->task),
        ];
    }
}
