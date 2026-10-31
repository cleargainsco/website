<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\User;
use App\Notifications\Orders\NewOrderNotification;
use App\Notifications\Orders\OrderCancelledNotification;
use App\Notifications\Orders\OrderPaidNotification;
use App\Notifications\Orders\OrderPlacedNotification;
use App\Notifications\Orders\OrderShippedNotification;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Notification;
use Shop\Events\OrderCancelled;
use Shop\Events\OrderPaid;
use Shop\Events\OrderPlaced;
use Shop\Events\OrderShipped;
use Shop\Models\Order;

/**
 * The shop raises an event at each step of an order; this sends the emails for them.
 */
final class SendOrderEmails
{
    public function placed(OrderPlaced $event): void
    {
        $this->customer($event->order)->notify(new OrderPlacedNotification($event->order));

        Notification::route('mail', config()->string('storefront.order_notifications_email'))
            ->notify(new NewOrderNotification($event->order));
    }

    public function paid(OrderPaid $event): void
    {
        $this->customer($event->order)->notify(new OrderPaidNotification($event->order));
    }

    public function shipped(OrderShipped $event): void
    {
        $this->customer($event->order)->notify(new OrderShippedNotification($event->order));
    }

    public function cancelled(OrderCancelled $event): void
    {
        $this->customer($event->order)->notify(new OrderCancelledNotification($event->order));
    }

    /**
     * @return array<class-string, string>
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            OrderPlaced::class => 'placed',
            OrderPaid::class => 'paid',
            OrderShipped::class => 'shipped',
            OrderCancelled::class => 'cancelled',
        ];
    }

    private function customer(Order $order): User
    {
        return User::query()->findOrFail($order->user_id);
    }
}
