<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Seo\UpdateSeoPageAction;
use App\Enums\Permission;
use App\Enums\SeoPage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSeoPageRequest;
use App\Models\SeoPageSetting;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class SeoPageController extends Controller
{
    public function index(): Response
    {
        $this->authorize(Permission::SeoManage->value);

        $settings = SeoPageSetting::query()->with('media')->get()->keyBy(fn (SeoPageSetting $setting): string => $setting->page->value);

        return Inertia::render('admin/seo/index', [
            'pages' => array_map(function (SeoPage $page) use ($settings): array {
                $setting = $settings->get($page->value);

                return [
                    'key' => $page->value,
                    'label' => $page->label(),
                    'url' => route($page->value),
                    'title' => $setting?->title,
                    'description' => $setting?->description,
                    'share_image_url' => $setting?->shareImageUrl(),
                    'default_title' => $page->defaultTitle(),
                    'default_description' => $page->defaultDescription(),
                ];
            }, SeoPage::cases()),
        ]);
    }

    public function update(UpdateSeoPageRequest $request, SeoPage $page, UpdateSeoPageAction $action): RedirectResponse
    {
        $action->handle($page, $request->toSeoPageData());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Saved the SEO for :page.', ['page' => $page->label()])]);

        return back();
    }
}
