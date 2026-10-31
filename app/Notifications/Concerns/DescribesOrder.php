<?php

declare(strict_types=1);

namespace App\Notifications\Concerns;

use Illuminate\Notifications\Messages\MailMessage;
use Shop\Models\Order;
use Shop\Models\OrderLine;

trait DescribesOrder
{
    protected function withOrderSummary(MailMessage $message, Order $order): MailMessage
    {
        $order->loadMissing('lines');

        $order->lines->each(function (OrderLine $line) use ($message): void {
            $amount = $line->product_details['amount'] ?? null;

            $message->line("{$line->quantity} × {$line->product_name}".($amount ? " {$amount}" : '').' — '.$line->line_total_minor->formatTo('en_US'));
        });

        $message->line('**Subtotal:** '.$order->subtotal_minor->formatTo('en_US'));

        if ($order->shipping_minor !== null) {
            $message->line('**Shipping:** '.$order->shipping_minor->formatTo('en_US'))
                ->line('**Total:** '.$order->total_minor->formatTo('en_US'));
        }

        return $message;
    }
}
