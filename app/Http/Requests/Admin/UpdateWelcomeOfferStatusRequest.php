<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\Permission;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateWelcomeOfferStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(Permission::MarketingManage->value) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'is_enabled' => ['required', 'boolean'],
        ];
    }
}
