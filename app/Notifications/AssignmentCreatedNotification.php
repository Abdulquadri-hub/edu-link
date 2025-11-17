<?php

namespace App\Notifications;

use App\Models\Assignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Actions\Action as FilamentAction;

class AssignmentCreatedNotification extends Notification
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
            ->subject('New Assignment - '. $this->assignment->title)
            ->greeting("Hello {$notifiable->first_name} !")
            ->line('A new assignment has been posted.')
            ->line('Course: ' . $this->assignment->course->title)
            ->line('Title: ' . $this->assignment->title)
            ->line('Due Date: ' . $this->assignment->due_at->format('M d, Y \a\t h:i: A'))
            ->line('Maximum Score: ' . $this->assignment->max_score . ' points')
            ->action('View Assignment', url('/student/assignments/' . $this->assignment->id))
            ->line('Good luck!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title("New Assignment")
            ->body("New assignment: {$this->assignment->title} is due at" . 
                         $this->assignment->due_at->format('M d, Y'))
            ->success()
            ->icon('heroicon-o-clipboard-document-list')
            ->actions([
                FilamentAction::make('view')
                        ->label('View Assignment')
                        ->url(route('filament.student.resources.assignments.view', $this->assignment->id)) 
                ])
            ->getDatabaseMessage();

        // return [
        //     'type' => 'assignment_created',
        //     'assignment_id' => $this->assignment->id,
        //     'title' => 'New Assignment',
        //     'body' => "New assignment '{$this->assignment->title}' is due " . 
        //                  $this->assignment->due_at->format('M d, Y'),
        //     'course_code' => $this->assignment->course->course_code,
        //     'due_at' => $this->assignment->due_at,
        //     'max_score' => $this->assignment->max_score,
        // ];
    }
}
