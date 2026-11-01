<?php

declare(strict_types=1);

namespace App\Notifications\Marketing;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Shop\Models\DiscountCode;

final class WelcomeOfferNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly DiscountCode $discountCode)
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
        $percent = $this->discountCode->percent_off;

        return (new MailMessage)
            ->subject("Your {$percent}% off code")
            ->greeting('Welcome to Clear Gains')
            ->line("Thanks for joining our list. Here's {$percent}% off your first order:")
            ->line("# {$this->discountCode->code}")
            ->line($this->discountCode->expires_at === null
                ? 'Enter it at checkout. It can be used once, on an order placed with this email address.'
                : 'Enter it at checkout by '.$this->discountCode->expires_at->format('F j, Y').'. It can be used once, on an order placed with this email address.')
            ->action('Shop now', route('shop.index'))
            ->line('Our products are sold for laboratory research use only.');
    }
}
