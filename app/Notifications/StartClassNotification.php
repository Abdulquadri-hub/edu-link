<?php

namespace App\Notifications;

use App\Models\Student;
use App\Models\ClassSession;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Actions\Action as FilamentAction;

class StartClassNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public ClassSession $classSession,
        public bool $isParent = false,
        public Student $student

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
        $message = (new MailMessage)
            ->subject('Class Started: ' . $this->classSession->title);

            if($this->isParent) {
                $message->greeting("Hello!")
                    ->line("Your child {$this->student->user->full_name}'s class has started.")
                    ->line('Course: ' . $this->classSession->course->title)
                    ->line('Class: ' . $this->classSession->title)
                    ->line('Time: ' . $this->classSession->scheduled_at);
            }else {
                $message->greeting('Hello!')
                    ->line('Your class has started.')
                    ->line('Course: ' . $this->classSession->course->title)
                    ->line('Class: ' . $this->classSession->title)
                    ->line('Time: ' . $this->classSession->scheduled_at);
            }

            if($this->classSession->google_meet_link) {
                $message->action('Join Class', $this->classSession->google_meet_link);
            }

            return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $body = $this->isParent 
           ? "Your child {$this->student->user->full_name}'s class {$this->classSession->title} has started at {$this->classSession->scheduled_at}"
           : "Class {$this->classSession->title} has started at {$this->classSession->scheduled_at}";

        $notification = FilamentNotification::make()
            ->title($this->isParent ? 'Child\'s Class Started' : 'Class Started')
            ->body($body)
            ->success()
            ->icon('heroicon-o-academic-cap');

        if (!$this->isParent && $this->classSession->google_meet_link) {
            $notification->actions([
                FilamentAction::make('join')
                    ->label('Join Now')
                    ->url($this->classSession->google_meet_link)
                    ->openUrlInNewTab(),
            ]);
        }

        return $notification->getDatabaseMessage();
    }
}
