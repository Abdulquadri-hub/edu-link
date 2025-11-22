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
            ->body(function () {
                $studentName = $this->linkingRequest->student->user->full_name;
                $parentName = $this->linkingRequest->parent->user->full_name;
                $isNewStudent = Str::startsWith($this->linkingRequest->student->student_id, 'TEMP-');

                if ($isNewStudent) {
                    return "{$parentName} submitted a **New Student Registration & Linking Request** for a student named {$studentName}.";
                }
                return "{$parentName} requested to link with {$studentName}";
            })
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
            ->line(function () {
                $isNewStudent = Str::startsWith($this->linkingRequest->student->student_id, 'TEMP-');
                if ($isNewStudent) {
                    return 'A new **Student Registration and Linking Request** has been submitted.';
                }
                return 'A new parent-child linking request has been submitted.';
            })
            ->line("Parent: {$this->linkingRequest->parent->user->full_name}")
            ->line("Student: {$this->linkingRequest->student->user->full_name}")
            ->line(function () {
                $isNewStudent = Str::startsWith($this->linkingRequest->student->student_id, 'TEMP-');
                if ($isNewStudent) {
                    return 'Status: **New Student - Pending Enrollment**';
                }
                return '';
            })
            ->line("Relationship: {$this->linkingRequest->relationship}")
            ->action('Review Request', route('filament.admin.resources.child-linking-requests.view', $this->linkingRequest->id));
    }
}
