<?php

declare(strict_types=1);

use App\Services\Resend\ResendClient;
use Resend\Client;
use Resend\Contracts\Transporter;
use Resend\Exceptions\ErrorException;
use Resend\ValueObjects\ApiKey;
use Resend\ValueObjects\Transporter\BaseUri;
use Resend\ValueObjects\Transporter\Headers;
use Resend\ValueObjects\Transporter\Payload;

/**
 * Answers each request from a script keyed by "METHOD path", and records what was asked.
 *
 * @param  array<string, array<string, mixed>|ErrorException>  $responses
 */
function scriptedResend(array $responses): object
{
    return new class($responses) implements Transporter
    {
        /** @var list<string> */
        public array $requests = [];

        /** @param array<string, array<string, mixed>|ErrorException> $responses */
        public function __construct(private array $responses) {}

        public function request(Payload $payload): array
        {
            $request = $payload->toRequest(BaseUri::from('api.resend.com'), Headers::withAuthorization(ApiKey::from('re_test')));
            $key = $request->getMethod().' '.mb_ltrim($request->getUri()->getPath(), '/');
            $this->requests[] = $key;

            $response = $this->responses[$key] ?? throw new RuntimeException("Unexpected request: {$key}");

            return $response instanceof ErrorException ? throw $response : $response;
        }
    };
}

test('creates a contact Resend has never seen, then adds it to each segment', function () {
    $transport = scriptedResend([
        'GET contacts/ada@example.com' => new ErrorException(['message' => 'Contact not found', 'name' => 'not_found', 'statusCode' => 404]),
        'POST contacts' => ['object' => 'contact', 'id' => 'ct_new'],
        'POST contacts/ct_new/segments/seg_1' => ['id' => 'seg_1'],
        'POST contacts/ct_new/segments/seg_2' => ['id' => 'seg_2'],
    ]);

    $id = (new ResendClient(new Client($transport)))->subscribeContact('ada@example.com', 'Ada', ['seg_1', 'seg_2']);

    expect($id)->toBe('ct_new')
        ->and($transport->requests)->toBe(['GET contacts/ada@example.com', 'POST contacts', 'POST contacts/ct_new/segments/seg_1', 'POST contacts/ct_new/segments/seg_2']);
});

test('adds an existing contact to the segment without recreating it', function () {
    $transport = scriptedResend([
        'GET contacts/ada@example.com' => ['object' => 'contact', 'id' => 'ct_old', 'unsubscribed' => true],
        'POST contacts/ct_old/segments/seg_1' => ['id' => 'seg_1'],
    ]);

    $id = (new ResendClient(new Client($transport)))->subscribeContact('ada@example.com', null, ['seg_1']);

    expect($id)->toBe('ct_old')
        ->and($transport->requests)->not->toContain('POST contacts');
});

test('lets any other error through, so the job retries', function () {
    $transport = scriptedResend([
        'GET contacts/ada@example.com' => new ErrorException(['message' => 'Internal error', 'name' => 'internal_server_error', 'statusCode' => 500]),
    ]);

    expect(fn () => (new ResendClient(new Client($transport)))->subscribeContact('ada@example.com', null, ['seg_1']))
        ->toThrow(ErrorException::class, 'Internal error');
});
