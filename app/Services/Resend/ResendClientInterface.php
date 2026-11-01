<?php

declare(strict_types=1);

namespace App\Services\Resend;

interface ResendClientInterface
{
    /**
     * Adds the contact to each segment, creating the contact first if Resend has never
     * seen the address. Returns Resend's contact id.
     *
     * @param  list<string>  $segmentIds
     */
    public function subscribeContact(string $email, ?string $firstName, array $segmentIds): string;
}
