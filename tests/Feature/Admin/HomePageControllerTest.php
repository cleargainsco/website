<?php

declare(strict_types=1);

use App\Models\HomePage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

function homePageForm(array $overrides = []): array
{
    return array_replace([
        'eyebrow' => 'Lab supply',
        'title' => 'Peptides you can',
        'title_highlight' => 'verify.',
        'description' => 'Every batch tested.',
        'image_alt' => 'A row of vials',
    ], $overrides);
}

test('the home page shows the default hero until an admin changes it', function () {
    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('marketing/home')
            ->where('hero.title', 'Clarity in')
            ->where('hero.title_highlight', 'every vial.')
            ->where('hero.image_url', null));
});

test('admins open the editor with the current hero', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.pages.home.edit'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/pages/home')
            ->where('hero.eyebrow', 'Research peptides')
            ->where('hero.image_url', null));
});

test('an admin rewrites the hero and uploads a photo, scaled down and stored as WebP', function () {
    Storage::fake('public');

    $this->actingAs(User::factory()->admin()->create())
        ->put(route('admin.pages.home.update'), homePageForm([
            'title_highlight' => '',
            'hero_image' => UploadedFile::fake()->image('hero.jpg', 3000, 2000),
        ]))
        ->assertSessionHasNoErrors();

    $media = HomePage::current()->getFirstMedia(HomePage::HERO_IMAGE_COLLECTION);
    [$width] = getimagesizefromstring(Storage::disk('public')->get($media->getPathRelativeToRoot()));

    expect($media->mime_type)->toBe('image/webp')
        ->and($width)->toBe(HomePage::HERO_IMAGE_MAX_WIDTH);

    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('hero.title', 'Peptides you can')
            ->where('hero.title_highlight', null)
            ->where('hero.description', 'Every batch tested.')
            ->where('hero.image_url', $media->getUrl()));
});

test('removing the photo puts the original back', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->admin()->create());

    $this->put(route('admin.pages.home.update'), homePageForm(['hero_image' => UploadedFile::fake()->image('hero.jpg')]));
    $this->put(route('admin.pages.home.update'), homePageForm(['remove_hero_image' => '1']));

    expect(HomePage::current()->heroImageUrl())->toBeNull();
});

test('the heading, description and photo description are required', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->put(route('admin.pages.home.update'), homePageForm(['title' => '', 'description' => '', 'image_alt' => '']))
        ->assertInvalid(['title', 'description', 'image_alt']);

    expect(HomePage::current()->title)->toBe('Clarity in');
});

test('customers cannot see or change the home page editor', function () {
    $this->actingAs(User::factory()->customer()->create());

    $this->get(route('admin.pages.home.edit'))->assertForbidden();
    $this->put(route('admin.pages.home.update'), homePageForm())->assertForbidden();

    expect(HomePage::current()->title)->toBe('Clarity in');
});
