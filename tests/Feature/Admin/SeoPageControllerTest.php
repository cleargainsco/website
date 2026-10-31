<?php

declare(strict_types=1);

use App\Enums\SeoPage;
use App\Models\SeoPageSetting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

test('admins see every page with its defaults and what has been changed', function () {
    SeoPageSetting::query()->create(['page' => SeoPage::Shop, 'title' => 'Buy peptides']);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.seo.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/seo/index')
            ->has('pages', count(SeoPage::cases()))
            ->where('pages.0.key', 'home')
            ->where('pages.0.title', null)
            ->where('pages.1.title', 'Buy peptides')
            ->where('pages.1.default_title', 'Shop research peptides'));
});

test('an admin saves a title, description and share image cropped for link previews', function () {
    Storage::fake('public');

    $this->actingAs(User::factory()->admin()->create())
        ->put(route('admin.seo.update', SeoPage::Faq), [
            'title' => 'Peptide FAQ',
            'description' => 'Answers about ordering.',
            'share_image' => UploadedFile::fake()->image('share.png', 2400, 2400),
        ])
        ->assertRedirect();

    $setting = SeoPageSetting::query()->sole();
    $media = $setting->getFirstMedia(SeoPageSetting::SHARE_IMAGE_COLLECTION);
    [$width, $height] = getimagesizefromstring(Storage::disk('public')->get($media->getPathRelativeToRoot()));

    expect($setting->page)->toBe(SeoPage::Faq)
        ->and($setting->title)->toBe('Peptide FAQ')
        ->and($setting->description)->toBe('Answers about ordering.')
        ->and($media->mime_type)->toBe('image/jpeg')
        ->and([$width, $height])->toBe([1200, 630]);

    $this->get(route('faq'))->assertSee('property="og:image" content="'.$media->getUrl().'"', false);
});

test('clearing the fields puts the defaults back, and the share image can be removed', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->admin()->create());

    $this->put(route('admin.seo.update', SeoPage::Faq), ['title' => 'Peptide FAQ', 'share_image' => UploadedFile::fake()->image('share.png')]);
    $this->put(route('admin.seo.update', SeoPage::Faq), ['title' => '', 'remove_share_image' => '1']);

    $setting = SeoPageSetting::query()->with('media')->sole();

    expect($setting->title)->toBeNull()
        ->and($setting->shareImageUrl())->toBeNull();
});

test('titles and descriptions longer than search results show are refused', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->put(route('admin.seo.update', SeoPage::Faq), ['title' => str_repeat('a', 71), 'description' => str_repeat('a', 161)])
        ->assertInvalid(['title', 'description']);

    expect(SeoPageSetting::query()->count())->toBe(0);
});

test('customers cannot see or change page seo', function () {
    $this->actingAs(User::factory()->customer()->create());

    $this->get(route('admin.seo.index'))->assertForbidden();
    $this->put(route('admin.seo.update', SeoPage::Faq), ['title' => 'Hacked'])->assertForbidden();

    expect(SeoPageSetting::query()->count())->toBe(0);
});
