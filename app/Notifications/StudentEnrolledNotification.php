<?php

namespace App\Notifications;

use App\Models\Enrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Actions\Action as FilamentAction;

class StudentEnrolledNotification extends Notification
{
    use Queueable;

    protected Enrollment $enrollment;

    public function __construct(Enrollment $enrollment)
    {
        $this->enrollment = $enrollment;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $student = $this->enrollment->student;
        $course = $this->enrollment->course;
        $isStudent = $notifiable->id === $student->user_id;
        
        $greeting = $isStudent 
            ? "Hello {$student->user->first_name}!"
            : "Hello {$notifiable->first_name}!";
        
        $message = $isStudent
            ? "Congratulations! You have been successfully enrolled in {$course->title}."
            : "Great news! Your child {$student->user->full_name} has been enrolled in {$course->title}.";
        
        return (new MailMessage)
            ->subject('Course Enrollment Confirmed')
            ->greeting($greeting)
            ->line($message)
            ->line('')
            ->line('**Course Details:**')
            ->line("Course: {$course->title}")
            ->line("Course Code: {$course->course_code}")
            ->line("Level: " . ucfirst($course->level))
            ->line("Duration: {$course->duration_weeks} weeks")
            ->line("Credit Hours: {$course->credit_hours}")
            ->line('')
            ->line('**What\'s Next:**')
            ->line('• Access course materials in your dashboard')
            ->line('• View upcoming class schedules')
            ->line('• Complete assignments on time')
            ->line('• Track your progress')
            ->line('')
            ->action($isStudent ? 'View My Courses' : 'View Child\'s Progress', 
                    $isStudent 
                        ? route('filament.student.resources.courses.index')
                        : route('filament.parent.pages.dashboard'))
            ->line('')
            ->line('We wish you a successful learning journey!')
            ->salutation('Best regards,')
            ->salutation('The EduLink Team');
    }

    public function toDatabase($notifiable): array
    {
        $student = $this->enrollment->student;
        $course = $this->enrollment->course;
        $isStudent = $notifiable->id === $student->user_id;
        
        $viewUrl = $isStudent
            ? route('filament.student.resources.courses.view', $course->id)
            : route('filament.parent.resources.children.view', $student->id);
        
        return FilamentNotification::make()
            ->title('Successfully Enrolled! 🎓')
            ->body($isStudent 
                ? "You are now enrolled in {$course->title}. Start learning today!"
                : "{$student->user->full_name} is now enrolled in {$course->title}")
            ->success()
            ->icon('heroicon-o-check-badge')
            ->actions([
                FilamentAction::make('view_course')
                    ->label($isStudent ? 'View Course' : 'View Child')
                    ->url($viewUrl)
                    ->button()
                    ->color('success'),
                FilamentAction::make('view_schedule')
                    ->label('View Schedule')
                    ->url($isStudent 
                        ? route('filament.student.resources.class-sessions.index')
                        : route('filament.parent.resources.upcoming-class-resouces.index'))
                    ->button(),
            ])
            ->getDatabaseMessage();
    }

    public function toArray($notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}