<?php

declare(strict_types=1);

use App\Enums\EmailPreview;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;

test('admins see every email the shop sends', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.emails.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/emails/index')
            ->has('emails', count(EmailPreview::cases()))
            ->where('emails.0.subject', "We've received order CG-10042"));
});

test('each email shows who it is from and who receives it', function () {
    config(['mail.reply_to.address' => null, 'mail.from' => ['address' => 'orders@cleargainsco.test', 'name' => 'Clear Gains'], 'storefront.order_notifications_email' => 'team@cleargainsco.test']);

    $this->actingAs(User::factory()->admin()->create(['email' => 'ada@example.com']))
        ->get(route('admin.emails.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('emails.0.from', 'Clear Gains <orders@cleargainsco.test>')
            ->where('emails.0.to', ['ada@example.com'])
            ->where('emails.1.to', ['team@cleargainsco.test'])
            ->where('emails.0.reply_to', []));
});

test('each email shows the global reply-to address', function () {
    config(['mail.reply_to' => ['address' => 'hello@cleargainsco.test', 'name' => 'Clear Gains']]);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.emails.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('emails.0.reply_to', ['Clear Gains <hello@cleargainsco.test>']));
});

test('sent emails carry the global reply-to address', function () {
    config(['mail.default' => 'array', 'mail.reply_to' => ['address' => 'hello@cleargainsco.test', 'name' => 'Clear Gains']]);

    Mail::raw('Hello', fn ($message) => $message->to('ada@example.com'));

    $sent = Mail::mailer('array')->getSymfonyTransport()->messages()->first()->getOriginalMessage();

    expect($sent->getReplyTo()[0]->getAddress())->toBe('hello@cleargainsco.test');
});

test('each email renders with the sample order', function (EmailPreview $email) {
    $this->actingAs(User::factory()->admin()->create(['name' => 'Ada Lovelace']))
        ->get(route('admin.emails.show', $email))
        ->assertOk()
        ->assertSee('images/email/clear-gains-logo.png', false);
})->with(EmailPreview::cases());

test('the preview can be framed by the admin page on the same site', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.emails.show', EmailPreview::OrderPlaced))
        ->assertHeader('Content-Security-Policy', "frame-ancestors 'self'");
});

test('the order summary lists the sample lines', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.emails.show', EmailPreview::OrderPlaced))
        ->assertSee('3 × BPC-157 10mg')
        ->assertSee('$135.00');
});

test('customers cannot preview emails', function () {
    $this->actingAs(User::factory()->customer()->create())
        ->get(route('admin.emails.index'))
        ->assertForbidden();

    $this->actingAs(User::factory()->customer()->create())
        ->get(route('admin.emails.show', EmailPreview::NewOrder))
        ->assertForbidden();
});
