<?php

namespace App\Listeners;

use App\Events\ChildLinkingRejected;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Notification;

class NotifyParentOfLinkingRejection  implements ShouldQueue
{
    public function handle(ChildLinkingRejected $event): void
    {
        $parent = $event->linkingRequest->parent->user;
        
        $parent->notify(new class($event->linkingRequest) extends \Illuminate\Notifications\Notification {
            use \Illuminate\Bus\Queueable;
            
            public function __construct(public $linkingRequest) {}
            
            public function via($notifiable): array
            {
                return ['database', 'mail'];
            }
            
            public function toDatabase($notifiable): array
            {
                return [
                    'title' => 'Child Linking Rejected',
                    'message' => "Your request to link with {$this->linkingRequest->student->user->full_name} was rejected",
                    'reason' => $this->linkingRequest->admin_notes,
                ];
            }
            
            public function toMail($notifiable): MailMessage
            {
                return (new MailMessage)
                    ->subject('Child Linking Request Rejected')
                    ->line('Your child linking request has been rejected.')
                    ->line("Student: {$this->linkingRequest->student->user->full_name}")
                    ->line("Reason: {$this->linkingRequest->admin_notes}");
            }
        });
    }
}
