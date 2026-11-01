<?php

namespace App\Actions\Fortify;

use App\Actions\Marketing\SubscribeRegisteredUserAction;
use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    public function __construct(private readonly SubscribeRegisteredUserAction $subscribe) {}

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
            'marketing_opt_in' => ['nullable', 'boolean'],
        ])->validate();

        $user = User::query()->create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
        ]);

        $user->assignRole(RoleName::Customer);

        $this->subscribe->handle($user, filter_var($input['marketing_opt_in'] ?? false, FILTER_VALIDATE_BOOLEAN));

        return $user;
    }
}
