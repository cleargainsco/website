<?php

declare(strict_types=1);

namespace App\Services\Resend;

use Resend\Client;
use Resend\Exceptions\ErrorException;

final readonly class ResendClient implements ResendClientInterface
{
    private const int NOT_FOUND = 404;

    public function __construct(private Client $resend) {}

    /**
     * An existing contact keeps its unsubscribed flag: someone who opted out in Resend
     * stays out until they opt back in there.
     */
    /**
     * @param  list<string>  $segmentIds
     */
    public function subscribeContact(string $email, ?string $firstName, array $segmentIds): string
    {
        try {
            $contact = $this->resend->contacts->get($email);
        } catch (ErrorException $exception) {
            throw_unless($exception->getErrorCode() === self::NOT_FOUND, $exception);

            $contact = $this->resend->contacts->create(array_filter([
                'email' => $email,
                'first_name' => $firstName,
                'unsubscribed' => false,
            ], fn (mixed $value): bool => $value !== null));
        }

        foreach ($segmentIds as $segmentId) {
            $this->resend->contacts->segments->add($contact->id, $segmentId);
        }

        return $contact->id;
    }
}
