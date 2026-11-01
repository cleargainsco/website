<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Data\WelcomeOfferData;
use App\Enums\Permission;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateWelcomeOfferRequest extends FormRequest
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
            'percent_off' => ['required', 'integer', 'min:1', 'max:90'],
            'valid_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'eyebrow' => ['required', 'string', 'max:60'],
            'headline' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:500'],
            'button_label' => ['required', 'string', 'max:40'],
            'success_headline' => ['required', 'string', 'max:120'],
            'success_body' => ['required', 'string', 'max:500'],
            'fine_print' => ['required', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'percent_off' => 'discount',
            'valid_days' => 'days the code is valid',
            'eyebrow' => 'small heading',
            'button_label' => 'button label',
            'success_headline' => 'thank-you headline',
            'success_body' => 'thank-you message',
            'fine_print' => 'fine print',
        ];
    }

    public function toWelcomeOfferData(): WelcomeOfferData
    {
        return WelcomeOfferData::from($this->validated());
    }
}
