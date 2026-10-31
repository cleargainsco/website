<?php

declare(strict_types=1);

namespace App\Notifications\Orders;

use App\Notifications\Concerns\DescribesOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Shop\Models\Order;

final class OrderPlacedNotification extends Notification implements ShouldQueue
{
    use DescribesOrder, Queueable;

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
            ->subject("We've received order {$this->order->number}")
            ->greeting('Thank you for your order')
            ->line("Order **{$this->order->number}** is placed and your items are reserved.");

        return $this->withOrderSummary($message, $this->order)
            ->line("Next, we'll email you an invoice with payment instructions. Shipping and any tax are confirmed on the invoice, and your order ships once payment is received.")
            ->action('View your order', route('account.orders.show', $this->order))
            ->line('Our products are sold for laboratory research use only.');
    }
}
