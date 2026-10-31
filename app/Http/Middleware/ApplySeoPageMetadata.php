<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\SeoPage;
use App\Models\SeoPageSetting;
use Closure;
use Illuminate\Http\Request;
use Laravel\Head\Facades\Head;
use Symfony\Component\HttpFoundation\Response;

final class ApplySeoPageMetadata
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $page = SeoPage::tryFrom((string) $request->route()?->getName());

        if ($page !== null && $request->isMethod('GET')) {
            $setting = SeoPageSetting::query()->with('media')->firstWhere('page', $page);
            $shareImage = $setting?->shareImageUrl();

            Head::title($setting->title ?? $page->defaultTitle())
                ->description($setting->description ?? $page->defaultDescription())
                ->when($shareImage !== null, fn ($head) => $head->ogImage(
                    (string) $shareImage,
                    width: SeoPageSetting::SHARE_IMAGE_WIDTH,
                    height: SeoPageSetting::SHARE_IMAGE_HEIGHT,
                ));
        }

        return $next($request);
    }
}
