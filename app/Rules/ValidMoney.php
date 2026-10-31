<?php

declare(strict_types=1);

namespace App\Rules;

use Brick\Math\Exception\MathException;
use Brick\Money\Exception\MoneyException;
use Brick\Money\Money;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * A major-units amount the currency can hold exactly: "112.50" passes for USD,
 * "112.505" does not. The currency decides the precision, so it is always passed in.
 */
final readonly class ValidMoney implements ValidationRule
{
    public function __construct(private string $currency, private ?string $min = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) && ! is_int($value)) {
            $fail('The :attribute must be an amount.');

            return;
        }

        try {
            $money = Money::of((string) $value, $this->currency);
        } catch (MathException|MoneyException) {
            $fail('The :attribute must be an amount in '.$this->currency.'.');

            return;
        }

        if ($this->min !== null && $money->isLessThan($this->min)) {
            $fail("The :attribute must be at least {$this->min}.");
        }
    }
}
