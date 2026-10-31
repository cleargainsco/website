<?php

declare(strict_types=1);

namespace App\Notifications\Orders;

use App\Notifications\Concerns\DescribesOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Shop\Models\Order;

final class NewOrderNotification extends Notification implements ShouldQueue
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
            ->subject("New order {$this->order->number} from {$this->order->shipping_name}")
            ->line("**{$this->order->shipping_name}** ({$this->order->email}) placed order **{$this->order->number}**. It's awaiting payment: send them an invoice.");

        return $this->withOrderSummary($message, $this->order)
            ->action('Open the order', route('admin.orders.show', $this->order));
    }
}
