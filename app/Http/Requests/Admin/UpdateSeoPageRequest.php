<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Data\SeoPageData;
use App\Enums\Permission;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateSeoPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(Permission::SeoManage->value) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:70'],
            'description' => ['nullable', 'string', 'max:160'],
            'share_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'remove_share_image' => ['boolean'],
        ];
    }

    public function toSeoPageData(): SeoPageData
    {
        return SeoPageData::from($this->validated());
    }
}
