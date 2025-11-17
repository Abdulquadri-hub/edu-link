<?php

namespace App\Notifications;

use App\Models\ClassSession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Actions\Action as FilamentAction;


class ClassReminderNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public ClassSession $classSession
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
            ->subject('Reminder: Class Tomorrow - ' . $this->classSession->title)
            ->greeting('Hello ' . $notifiable->first_name . '!')
            ->line('This is a reminder that you have a class tomorrow.')
            ->line('Course: ' . $this->classSession->course->title)
            ->line('Title: ' . $this->classSession->title)
            ->line('Date: ' . $this->classSession->scheduled_at->format('M d, Y'))
            ->line('Time: ' . $this->classSession->scheduled_at->format('h:i A'))
            ->when($this->classSession->google_meet_link, function ($message) {
                return $message->action('Join Class', $this->classSession->google_meet_link);
            })
            ->line('Don\'t forget to prepare!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title('This is a reminder that you have a class tomorrow.')
            ->body("Class {$this->classSession->title} scheduled for {$this->classSession->scheduled_at->format('M d, Y \\a\\t h:i A')}")
            ->info()
            ->icon('heroicon-o-academic-cap')
            ->actions([
                FilamentAction::make('join')
                    ->label('Join Class')
                    ->url($this->classSession->google_meet_link)
                    ->openUrlInNewTab(),
            ])
            ->getDatabaseMessage();

        // return [
        //     'type' => 'class_reminder',
        //     'class_session_id' => $this->classSession->id,
        //     'title' => 'New Class Scheduled', 
        //     'body' => "Class '{$this->classSession->title}' scheduled for" . $this->classSession->scheduled_at->format('M d, Y \a\t h:i A'),
        //     'course_code' => $this->classSession->course->course_code,
        //     'scheduled_at' => $this->classSession->scheduled_at,
        //     'google_meet_link' => $this->classSession->google_meet_link
        // ];
    }
}
