<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Student;
use App\Models\StudentPromotion;

class StudentPromotedToNextLevel extends Notification
{
    use Queueable;

    public $student;
    public $promotion;

    public function __construct(Student $student, StudentPromotion $promotion)
    {
        $this->student = $student;
        $this->promotion = $promotion;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $from = $this->promotion->fromAcademicLevel?->name ?? 'N/A';
        $to = $this->promotion->toAcademicLevel?->name ?? 'N/A';

        return (new MailMessage)
                    ->subject('Student Promotion')
                    ->line("{$this->student->user->full_name} has been promoted from {$from} to {$to}.")
                    ->line($this->promotion->reason ? 'Reason: '. $this->promotion->reason : '')
                    ->line('If you have any questions, please contact the school administration.');
    }

    public function toArray($notifiable)
    {
        return [
            'student_id' => $this->student->id,
            'from' => $this->promotion->fromAcademicLevel?->name,
            'to' => $this->promotion->toAcademicLevel?->name,
            'reason' => $this->promotion->reason,
        ];
    }
}
