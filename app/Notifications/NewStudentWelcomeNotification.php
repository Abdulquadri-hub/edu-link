<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class NewStudentWelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User $studentUser,
        public string $tempPassword
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        // Generate a temporary signed URL for email verification
        // This assumes the Laravel application has the standard email verification routes set up.
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60), // Link expires in 60 minutes
            [
                'id' => $this->studentUser->id,
                'hash' => sha1($this->studentUser->getEmailForVerification()),
            ]
        );

        return (new MailMessage)
            ->subject('Welcome to EduLink! Your Student Account Details')
            ->greeting("Hello {$this->studentUser->first_name},")
            ->line('Your parent has successfully created a student account for you on the EduLink platform.')
            ->line('Before you can log in, you need to verify your email address.')
            ->action('Verify Email Address', $verificationUrl)
            ->line('---')
            ->line('**Your Temporary Login Credentials:**')
            ->line('**Email:** ' . $this->studentUser->email)
            ->line('**Temporary Password:** ' . $this->tempPassword)
            ->line('---')
            ->line('**Important:** For security, you must change this temporary password immediately after your first login.')
            ->line('After verifying your email, you can log in using the credentials above.')
            ->line('If you have any issues, please contact your school administrator.');
    }

    public function toArray($notifiable): array
    {
        return [
            //
        ];
    }
}
