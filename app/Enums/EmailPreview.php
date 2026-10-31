<?php

declare(strict_types=1);

namespace App\Enums;

use App\Models\User;
use App\Notifications\Orders\NewOrderNotification;
use App\Notifications\Orders\OrderCancelledNotification;
use App\Notifications\Orders\OrderPaidNotification;
use App\Notifications\Orders\OrderPlacedNotification;
use App\Notifications\Orders\OrderShippedNotification;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Notifications\Messages\MailMessage;
use Shop\Models\Order;

enum EmailPreview: string
{
    case OrderPlaced = 'order-placed';
    case NewOrder = 'new-order';
    case OrderPaid = 'order-paid';
    case OrderShipped = 'order-shipped';
    case OrderCancelled = 'order-cancelled';
    case VerifyEmail = 'verify-email';
    case ResetPassword = 'reset-password';

    public function label(): string
    {
        return match ($this) {
            self::OrderPlaced => 'Order received',
            self::NewOrder => 'New order alert',
            self::OrderPaid => 'Payment received',
            self::OrderShipped => 'Order shipped',
            self::OrderCancelled => 'Order cancelled',
            self::VerifyEmail => 'Verify email address',
            self::ResetPassword => 'Reset password',
        };
    }

    public function trigger(): string
    {
        return match ($this) {
            self::OrderPlaced => 'Sent to the customer when they place an order.',
            self::NewOrder => 'Sent to the shop when an order is placed.',
            self::OrderPaid => 'Sent to the customer when an order is marked paid.',
            self::OrderShipped => 'Sent to the customer when an order is marked shipped.',
            self::OrderCancelled => 'Sent to the customer when an order is cancelled.',
            self::VerifyEmail => 'Sent when someone registers or changes their email.',
            self::ResetPassword => 'Sent when someone asks to reset their password.',
        };
    }

    public function recipient(User $user): string
    {
        return match ($this) {
            self::NewOrder => config()->string('storefront.order_notifications_email'),
            default => $user->email,
        };
    }

    public function message(User $user, Order $order): MailMessage|Mailable
    {
        return match ($this) {
            self::OrderPlaced => (new OrderPlacedNotification($order))->toMail($user),
            self::NewOrder => (new NewOrderNotification($order))->toMail($user),
            self::OrderPaid => (new OrderPaidNotification($order))->toMail($user),
            self::OrderShipped => (new OrderShippedNotification($order))->toMail($user),
            self::OrderCancelled => (new OrderCancelledNotification($order))->toMail($user),
            self::VerifyEmail => (new VerifyEmail)->toMail($user),
            self::ResetPassword => (new ResetPassword('preview'))->toMail($user),
        };
    }
}
