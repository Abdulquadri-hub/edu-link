<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Enrollment;

class EnrollmentApproved extends Notification
{
    use Queueable;

    public Enrollment $enrollment;

    public function __construct(Enrollment $enrollment)
    {
        $this->enrollment = $enrollment;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $course = $this->enrollment->course;
        return (new MailMessage)
                    ->subject('Enrollment Approved')
                    ->line('Your enrollment for ' . $course->title . ' has been approved.')
                    ->line('You can now access course materials and schedules in your student portal.');
    }

    public function toArray($notifiable)
    {
        return [
            'enrollment_id' => $this->enrollment->id,
            'course_id' => $this->enrollment->course_id,
            'status' => $this->enrollment->status,
        ];
    }
}
