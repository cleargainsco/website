<?php

declare(strict_types=1);

namespace App\Http\Requests\Storefront;

use Illuminate\Foundation\Http\FormRequest;

final class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'shipping_name' => ['required', 'string', 'max:255'],
            'shipping_phone' => ['nullable', 'string', 'max:50'],
            'shipping_line1' => ['required', 'string', 'max:255'],
            'shipping_line2' => ['nullable', 'string', 'max:255'],
            'shipping_city' => ['required', 'string', 'max:255'],
            'shipping_region' => ['required', 'string', 'max:255'],
            'shipping_postal_code' => ['required', 'string', 'max:20'],
            'shipping_country' => ['required', 'string', 'max:100'],
            'customer_note' => ['nullable', 'string', 'max:2000'],
            'research_use' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'shipping_name' => 'name',
            'shipping_phone' => 'phone',
            'shipping_line1' => 'address',
            'shipping_line2' => 'address line 2',
            'shipping_city' => 'city',
            'shipping_region' => 'state or region',
            'shipping_postal_code' => 'ZIP or postal code',
            'shipping_country' => 'country',
            'customer_note' => 'note',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'research_use.accepted' => 'Confirm these products are for laboratory research only.',
        ];
    }
}
