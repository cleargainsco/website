<?php

declare(strict_types=1);

namespace App\Notifications\Orders;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Shop\Models\Order;

final class OrderCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Order $order)
    {
        $this->afterCommit();
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Order {$this->order->number} has been cancelled")
            ->line("Order **{$this->order->number}** has been cancelled.")
            ->line("If you've already paid, or think this is a mistake, reply to this email and we'll sort it out.");
    }
}
