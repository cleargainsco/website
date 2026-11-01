<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Resend\ResendClient;
use App\Services\Resend\ResendClientInterface;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Resend;

final class ResendServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        $this->app->bind(ResendClientInterface::class, fn (): ResendClient => new ResendClient(
            Resend::client(config()->string('services.resend.key')),
        ));
    }

    /**
     * @return list<class-string>
     */
    public function provides(): array
    {
        return [ResendClientInterface::class];
    }
}
