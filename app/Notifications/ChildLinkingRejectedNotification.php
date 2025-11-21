<?php

namespace App\Notifications;


use App\Models\ChildLinkingRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Actions\Action as FilamentAction;

class ChildLinkingRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public ChildLinkingRequest $linkingRequest
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray($notifiable): array
    {
        return FilamentNotification::make()
            ->title('Child Linking Rejected')
            ->body("Your request to link with {$this->linkingRequest->student->user->full_name} was rejected")
            ->danger()
            ->icon('heroicon-o-x-circle')
            ->body("Reason: {$this->linkingRequest->admin_notes}")
            ->getDatabaseMessage();
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Child Linking Request Rejected')
            ->line('Your child linking request has been rejected.')
            ->line("Student: {$this->linkingRequest->student->user->full_name}")
            ->line("Reason: {$this->linkingRequest->admin_notes}");
    }
}