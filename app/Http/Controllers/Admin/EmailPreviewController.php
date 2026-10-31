<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\EmailPreview;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\SampleOrder;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Notifications\Messages\MailMessage;
use Inertia\Inertia;
use Inertia\Response;
use Shop\Models\Order;

final class EmailPreviewController extends Controller
{
    public function index(#[CurrentUser] User $user): Response
    {
        $this->authorize('viewAny', Order::class);

        $order = SampleOrder::forCustomer($user);

        return Inertia::render('admin/emails/index', [
            'emails' => array_map(function (EmailPreview $email) use ($user, $order): array {
                $message = $email->message($user, $order);

                $isMailMessage = $message instanceof MailMessage;

                return [
                    'key' => $email->value,
                    'label' => $email->label(),
                    'trigger' => $email->trigger(),
                    'subject' => $isMailMessage ? $message->subject : null,
                    'from' => self::address($isMailMessage && $message->from !== [] ? $message->from : [config()->string('mail.from.address'), config()->string('mail.from.name')]),
                    'to' => [$email->recipient($user)],
                    'cc' => $isMailMessage ? array_map(self::address(...), $message->cc) : [],
                    'bcc' => $isMailMessage ? array_map(self::address(...), $message->bcc) : [],
                    'reply_to' => $isMailMessage ? array_map(self::address(...), $message->replyTo) : [],
                ];
            }, EmailPreview::cases()),
        ]);
    }

    public function show(#[CurrentUser] User $user, EmailPreview $email): MailMessage|Mailable
    {
        $this->authorize('viewAny', Order::class);

        return $email->message($user, SampleOrder::forCustomer($user));
    }

    /**
     * @param  array{0: array<mixed>|string, 1?: string|null}  $address
     */
    private static function address(array $address): string
    {
        if (is_array($address[0])) {
            return implode(', ', array_filter($address[0], is_string(...)));
        }

        return isset($address[1]) && $address[1] !== '' ? "{$address[1]} <{$address[0]}>" : $address[0];
    }
}
