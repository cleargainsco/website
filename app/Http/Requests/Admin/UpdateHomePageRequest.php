<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Data\HomePageData;
use App\Enums\Permission;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateHomePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(Permission::ContentManage->value) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'eyebrow' => ['required', 'string', 'max:60'],
            'title' => ['required', 'string', 'max:80'],
            'title_highlight' => ['nullable', 'string', 'max:80'],
            'description' => ['required', 'string', 'max:400'],
            'image_alt' => ['required', 'string', 'max:160'],
            'hero_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'remove_hero_image' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'eyebrow' => 'small heading',
            'title' => 'heading',
            'title_highlight' => 'highlighted words',
            'image_alt' => 'image description',
            'hero_image' => 'image',
        ];
    }

    public function toHomePageData(): HomePageData
    {
        return HomePageData::from($this->validated());
    }
}
