<?php

namespace App\Notifications;

use App\Models\ParentRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;

class ParentAccountCreatedNotification extends Notification
{
    use Queueable;

    protected ParentRegistration $registration;
    protected string $temporaryPassword;

    public function __construct(ParentRegistration $registration, string $temporaryPassword)
    {
        $this->registration = $registration;
        $this->temporaryPassword = $temporaryPassword;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $student = $this->registration->student;
        $loginUrl = route('filament.parent.auth.login');
        $expiryDays = $this->registration->days_until_expiry;
        
        return (new MailMessage)
            ->subject('Welcome to EduLink - Parent Account Created')
            ->greeting("Hello {$this->registration->parent_first_name}!")
            ->line("Your child, **{$student->user->full_name}**, has requested to enroll in a course at EduLink.")
            ->line("We've created a parent account for you to manage their education and make payments.")
            ->line('')
            ->line('**Your Account Details:**')
            ->line("Email: {$this->registration->parent_email}")
            ->line("Temporary Password: `{$this->temporaryPassword}`")
            ->line("Registration Code: {$this->registration->registration_code}")
            ->line('')
            ->line('**What You Need to Do:**')
            ->line('1. Click the button below to log in')
            ->line('2. Update your password (required on first login)')
            ->line('3. Review the enrollment request')
            ->line('4. Upload payment receipt for the course')
            ->line('5. Wait for payment verification (1-2 business days)')
            ->line('6. Your child will be enrolled once payment is verified!')
            ->line('')
            ->action('Log In to Parent Portal', $loginUrl)
            ->line('')
            ->line('**Important Notes:**')
            ->line("• This registration link expires in **{$expiryDays} days**")
            ->line('• You must change your password on first login')
            ->line('• Keep your login credentials secure')
            ->line('• Contact administration if you have any questions')
            ->line('')
            ->line('**Student Information:**')
            ->line("Name: {$student->user->full_name}")
            ->line("Student ID: {$student->student_id}")
            ->line("Email: {$student->user->email}")
            ->line('')
            ->line('Thank you for being part of the EduLink community!')
            ->salutation('Best regards,')
            ->salutation('The EduLink Team');
    }

    public function toArray($notifiable): array
    {
        return [
            'registration_id' => $this->registration->id,
            'registration_code' => $this->registration->registration_code,
            'student_name' => $this->registration->student->user->full_name,
            'temporary_password' => $this->temporaryPassword,
        ];
    }
}