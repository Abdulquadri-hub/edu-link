<?php

namespace App\Notifications;


use App\Models\ChildLinkingRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Actions\Action as FilamentAction;

class ChildLinkingApprovedNotification extends Notification
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
            ->title('Child Linking Approved')
            ->body("Your request to link with {$this->linkingRequest->student->user->full_name} has been approved!")
            ->success()
            ->icon('heroicon-o-check-circle')
            ->actions([
                FilamentAction::make('view_child')
                    ->label('View Child Profile')
                    ->url(route('filament.parent.resources.children.view', $this->linkingRequest->student_id))
                    ->button()
                    ->color('success'),
            ])
            ->getDatabaseMessage();
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Child Linking Request Approved')
            ->line('Great news! Your child linking request has been approved.')
            ->line("You are now linked to: {$this->linkingRequest->student->user->full_name}")
            ->line("Relationship: {$this->linkingRequest->relationship}")
            ->action('View Child Profile', route('filament.parent.resources.children.view', $this->linkingRequest->student_id));
    }
}
