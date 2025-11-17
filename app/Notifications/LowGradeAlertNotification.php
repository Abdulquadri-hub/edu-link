<?php

namespace App\Notifications;

use App\Models\Grade;
use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowGradeAlertNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Grade $grade,
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
        return (new MailMessage)
            ->subject('Grade Alert for ' . $this->student->user->full_name)
            ->greeting('Hello ' . $notifiable->first_name . ',')
            ->line('This is to inform you about a recent grade for your child.')
            ->line('**Student:** ' . $this->student->user->full_name)
            ->line('**Assignment:** ' . $this->grade->submission->assignment->title)
            ->line('**Course:** ' . $this->grade->submission->assignment->course->title)
            ->line('**Score:** ' . $this->grade->percentage . '% (' . $this->grade->letter_grade . ')')
            ->line('You may want to check in with your child about this assignment.')
            ->action('View Details', url('/parent/children/' . $this->student->id . '/grades'))
            ->line('Thank you for staying involved in your child\'s education.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'low_grade_alert',
            'student_id' => $this->student->id,
            'grade_id' => $this->grade->id,
            'title' => 'Grade Alert',
            'body' => "{$this->student->user->full_name} scored {$this->grade->percentage}% on '{$this->grade->submission->assignment->title}'",
            'student_name' => $this->student->user->full_name,
            'assignment_title' => $this->grade->submission->assignment->title,
            'percentage' => $this->grade->percentage,
        ];
    }
}
