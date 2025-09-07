<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssignTask extends Notification
{
    use Queueable;

    protected $task;
    protected $team_id;
    protected $board_id;
    /**
     * Create a new notification instance.
     */
    public function __construct($task, $team_id, $board_id)
    {
        $this->task = $task;
        $this->team_id = $team_id;
        $this->board_id = $board_id;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Task Assigned to You')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line("You have been assigned a new task: **{$this->task->name}**.")
            ->line("Please review the task details and start working on it.")
            ->action('View Task', url("team/{$this->team_id}/board/{$this->board_id}/card/{$this->task->id}/view"))
            ->line('Thank you for your dedication and effort!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
