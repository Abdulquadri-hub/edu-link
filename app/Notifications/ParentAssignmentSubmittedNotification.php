<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use App\Models\ParentAssignment;
use Filament\Actions\Action;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Actions\Action as FilamentAction;

class ParentAssignmentSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public ParentAssignment $parentAssignment
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Parent Assignment Upload')
            ->line("{$this->parentAssignment->parent->user->full_name} has uploaded an assignment.")
            ->line("Student: {$this->parentAssignment->student->user->full_name}")
            ->line("Assignment: {$this->parentAssignment->assignment->title}")
            ->line("Course: {$this->parentAssignment->assignment->course->title}")
            ->action('View Assignment', route('filament.instructor.resources.parent-uploaded-assignments.view', $this->parentAssignment->id));
    }

    
    public function toArray($notifiable): array
    {
        return FilamentNotification::make()
            ->title('Parent Uploaded Assignment')
            ->body("{$this->parentAssignment->parent->user->full_name} uploaded an assignment for {$this->parentAssignment->student->user->full_name}")
            ->info()
            ->icon('heroicon-o-document-arrow-up')
            ->actions([
                FilamentAction::make('view')
                    ->label('View Assignment')
                    ->url(route('filament.instructor.resources.parent-uploaded-assignments.view', $this->parentAssignment->id))
                    ->button(),
            ])
            ->getDatabaseMessage();
    }
}