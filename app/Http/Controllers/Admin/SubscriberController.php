<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use Inertia\Inertia;
use Inertia\Response;

final class SubscriberController extends Controller
{
    public function index(): Response
    {
        $this->authorize(Permission::MarketingManage->value);

        $subscribers = Subscriber::query()
            ->with('discountCode')
            ->latest()
            ->paginate(50)
            ->through(fn (Subscriber $subscriber): array => [
                'id' => $subscriber->id,
                'email' => $subscriber->email,
                'source' => $subscriber->source,
                'source_label' => $subscriber->source->label(),
                'has_account' => $subscriber->user_id !== null,
                'marketing_consent_at' => $subscriber->marketing_consent_at?->toIso8601String(),
                'discount_code' => $subscriber->discountCode?->code,
                'discount_code_used' => $subscriber->discountCode?->isUsedUp() ?? false,
                'synced_to_resend_at' => $subscriber->synced_to_resend_at?->toIso8601String(),
                'created_at' => $subscriber->created_at?->toIso8601String(),
            ]);

        return Inertia::render('admin/subscribers/index', [
            'subscribers' => $subscribers,
            'totals' => [
                'all' => Subscriber::query()->count(),
                'consented' => Subscriber::query()->whereNotNull('marketing_consent_at')->count(),
                'synced' => Subscriber::query()->whereNotNull('synced_to_resend_at')->count(),
            ],
            'resend_configured' => filled(config('services.resend.segments.general')),
        ]);
    }
}
