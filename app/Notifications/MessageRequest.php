<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class MessageRequest extends Notification
{
    use Queueable;
    public $event;
    public $friend_id;

    /**
     * Create a new notification instance.
     */
    public function __construct($event, $friend_id)
    {
        $this->event = $event;
        $this->friend_id = $friend_id;

    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['Broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        Log::info('Event sent from ' . $this->friend_id);
        return (new BroadcastMessage([
            'event' => $this->event,  
            'friend_id' => $this->friend_id,
        ]));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'event' => $this->event,  
            'friend_id' => $this->friend_id,
        ];
    }
}
