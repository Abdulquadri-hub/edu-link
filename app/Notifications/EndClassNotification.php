<?php

namespace App\Notifications;

use App\Models\Student;
use App\Models\ClassSession;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Filament\Actions\Action as FilamentAction;
use Illuminate\Notifications\Messages\MailMessage;
use Filament\Notifications\Notification as FilamentNotification;

class EndClassNotification extends Notification
{
    use Queueable;
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
            ->subject('Class Ended: ' . $this->classSession->title);

            if($this->isParent) {
                $message->greeting("Hello!")
                    ->line("Your child {$this->student->user->full_name}'s class has ended.")
                    ->line('Course: ' . $this->classSession->course->title)
                    ->line('Class: ' . $this->classSession->title)
                    ->line('Time: ' . $this->classSession->scheduled_at);
            }else {
                $message->greeting('Hello!')
                    ->line('Your class has ended.')
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
            ->title($this->isParent ? 'Child\'s Class Ended' : 'Class Ended')
            ->body($body)
            ->info()
            ->icon('heroicon-o-check-circle');

        return $notification->getDatabaseMessage();
    }
}
