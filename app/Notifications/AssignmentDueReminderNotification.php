<?php

namespace App\Notifications;

use App\Models\Assignment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Actions\Action as FilamentAction;

class AssignmentDueReminderNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Assignment $assignment
    )
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Assignment Due Tomorrow: ' . $this->assignment->title)
            ->greeting('Hello ' . $notifiable->first_name . '!')
            ->line('This is a reminder that an assignment is due tomorrow.')
            ->line('**Assignment:** ' . $this->assignment->title)
            ->line('**Due:** ' . $this->assignment->due_at->format('M d, Y \a\t h:i A'))
            ->line('**Maximum Score:** ' . $this->assignment->max_score . ' points')
            ->action('Submit Now', url('/student/assignments/' . $this->assignment->id))
            ->line('Don\'t miss the deadline!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title("Assignment Due Reminder")
            ->body("{$this->assignment->title} is due tomorrow at " . 
                         $this->assignment->due_at->format('h:i A'))
            ->info()
            ->icon('heroicon-o-clipboard-document-list')
            ->actions([
                FilamentAction::make('view')
                        ->label('View Assignment')
                        ->url(route('filament.student.resources.assignments.view', $this->assignment->id)) 
                ])
            ->getDatabaseMessage();

        // return [
        //     'type' => 'assignment_due_reminder',
        //     'assignment_id' => $this->assignment->id,
        //     'title' => 'Assignment Due Tomorrow',
        //     'body' => "'{$this->assignment->title}' is due tomorrow at " . 
        //                  $this->assignment->due_at->format('h:i A'),
        //     'assignment_title' => $this->assignment->title,
        //     'due_at' => $this->assignment->due_at,
        // ];
    }
}
