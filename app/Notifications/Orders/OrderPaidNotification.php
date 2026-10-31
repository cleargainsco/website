<?php

declare(strict_types=1);

namespace App\Notifications\Orders;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Shop\Models\Order;

final class OrderPaidNotification extends Notification implements ShouldQueue
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
            ->subject("Payment received for order {$this->order->number}")
            ->line("We've received payment for order **{$this->order->number}**. We're packing it now and will email you tracking details when it ships.")
            ->action('View your order', route('account.orders.show', $this->order));
    }
}
