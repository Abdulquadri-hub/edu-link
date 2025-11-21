<?php

namespace App\Notifications;

use App\Models\ChildLinkingRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Actions\Action as FilamentAction;

class ChildLinkingRequestNotification extends Notification
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
            ->title('New Child Linking Request')
            ->body("{$this->linkingRequest->parent->user->full_name} requested to link with {$this->linkingRequest->student->user->full_name}")
            ->warning()
            ->icon('heroicon-o-user-plus')
            ->actions([
                FilamentAction::make('review')
                    ->label('Review Request')
                    ->url(route('filament.admin.resources.child-linking-requests.view', $this->linkingRequest->id))
                    ->button()
                    ->color('warning'),
            ])
            ->getDatabaseMessage();
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Child Linking Request')
            ->line('A new parent-child linking request has been submitted.')
            ->line("Parent: {$this->linkingRequest->parent->user->full_name}")
            ->line("Student: {$this->linkingRequest->student->user->full_name}")
            ->line("Relationship: {$this->linkingRequest->relationship}")
            ->action('Review Request', route('filament.admin.resources.child-linking-requests.view', $this->linkingRequest->id));
    }
}
