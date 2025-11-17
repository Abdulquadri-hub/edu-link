<?php

namespace App\Notifications;

use App\Models\Grade;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GradePublishedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Grade $grade
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
            ->subject('Your Grade Has Been Published')
            ->greeting("Hello $notifiable->fist_name !")
            ->line('Your grade for an assignment has been published')
            ->line('Assignment: '. $this->grade->submission->assignment->title)
            ->line('Score: ' . $this->grade->score . '/' . $this->grade->max_score)
            ->line('Percentage: ' . $this->grade->percentage . '%')
            ->line('Grade: ' . $this->grade->letter_grade)
            ->when($this->grade->feedback, function ($message) {
                return $message->line('**Feedback:** ' . $this->grade->feedback);
            })
            ->action('View Grade', url('/student/grades/' . $this->grade->id))
            ->line('Keep up the good work!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'grade_published',
            'grade_id' => $this->grade->id,
            'title' => 'Grade Published',
            'body' => "You scored {$this->grade->percentage}% ({$this->grade->letter_grade}) on '{$this->grade->submission->assignment->title}'",
            'assignment_title' => $this->grade->submission->assignment->title,
            'score' => $this->grade->score,
            'max_score' => $this->grade->max_score,
            'percentage' => $this->grade->percentage,
            'letter_grade' => $this->grade->letter_grade,
        ];
    }
}
