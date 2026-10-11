<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TradeInquiry extends Notification
{
    public function __construct(public array $inquiry) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Juanovio — '.config('juanovio.types.'.$this->inquiry['type']))
            ->replyTo($this->inquiry['email'])
            ->view('mail.inquiry', ['inquiry' => $this->inquiry]);
    }
}
