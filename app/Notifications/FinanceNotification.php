<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class FinanceNotification extends Notification
{
    use Queueable;
    private $notification;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($notification)
    {
        //
        $this->notification = $notification;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
       
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
            "message" => $this->notification['message'],
            "titre" => $this->notification['titre'] ?? '',
            "membre" => $this->notification['membre'],
            "operation" => $this->notification['operation'],
            "association" => $this->notification['association'],
            "type" => "Finance"
        ];
    }
}
