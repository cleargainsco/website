<?php

declare(strict_types=1);

namespace App\Notifications\Orders;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Shop\Models\Order;

final class OrderShippedNotification extends Notification implements ShouldQueue
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
        $message = (new MailMessage)
            ->subject("Order {$this->order->number} has shipped")
            ->line("Order **{$this->order->number}** is on its way.");

        if ($this->order->tracking_number !== null) {
            $message->line('**Tracking:** '.trim(($this->order->carrier ?? '').' '.$this->order->tracking_number));
        }

        return $message->action('View your order', route('account.orders.show', $this->order));
    }
}
