<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Enrollment;

class EnrollmentPendingPayment extends Notification
{
    use Queueable;

    public Enrollment $enrollment;
    public float $price;
    public int $frequency;

    public function __construct(Enrollment $enrollment, int $frequency, float $price)
    {
        $this->enrollment = $enrollment;
        $this->price = $price;
        $this->frequency = $frequency;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $course = $this->enrollment->course;
        return (new MailMessage)
                    ->subject('Enrollment Pending Payment')
                    ->line('Thank you for enrolling in ' . $course->title)
                    ->line('Fee: $' . number_format($this->price, 2))
                    ->line('Frequency: ' . $this->frequency . 'x per week')
                    ->line('Please complete the payment externally as per the instructions provided. Do not send card details through this system.')
                    ->line('When you have completed payment, upload a receipt in the Parent or Student portal for admin review.');
    }

    public function toArray($notifiable)
    {
        return [
            'enrollment_id' => $this->enrollment->id,
            'course_id' => $this->enrollment->course_id,
            'price' => $this->price,
            'frequency' => $this->frequency,
        ];
    }
}
