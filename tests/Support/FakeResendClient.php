<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Services\Resend\ResendClientInterface;

final class FakeResendClient implements ResendClientInterface
{
    /**
     * @var list<array{email: string, first_name: string|null, segment_ids: list<string>}>
     */
    public array $subscribed = [];

    public function subscribeContact(string $email, ?string $firstName, array $segmentIds): string
    {
        $this->subscribed[] = ['email' => $email, 'first_name' => $firstName, 'segment_ids' => $segmentIds];

        return 'contact_'.count($this->subscribed);
    }
}
