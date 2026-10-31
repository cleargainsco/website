<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Laravel\Head\Facades\Head;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();
    }

    /**
     * Configure Fortify actions.
     */
    private function configureActions(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::createUsersUsing(CreateNewUser::class);
    }

    /**
     * Configure Fortify views.
     */
    private function configureViews(): void
    {
        Fortify::loginView(function (Request $request) {
            Head::title('Log in')->robots('noindex, nofollow');

            return Inertia::render('auth/login', [
                'canResetPassword' => Features::enabled(Features::resetPasswords()),
                'status' => $request->session()->get('status'),
            ]);
        });

        Fortify::resetPasswordView(function (Request $request) {
            Head::title('Reset password')->robots('noindex, nofollow');

            return Inertia::render('auth/reset-password', [
                'email' => $request->email,
                'token' => $request->route('token'),
                'passwordRules' => Password::defaults()->toPasswordRulesString(),
            ]);
        });

        Fortify::requestPasswordResetLinkView(function (Request $request) {
            Head::title('Forgot password')->robots('noindex, nofollow');

            return Inertia::render('auth/forgot-password', [
                'status' => $request->session()->get('status'),
            ]);
        });

        Fortify::verifyEmailView(function (Request $request) {
            Head::title('Email verification')->robots('noindex, nofollow');

            return Inertia::render('auth/verify-email', [
                'status' => $request->session()->get('status'),
            ]);
        });

        Fortify::registerView(function () {
            Head::title('Create an account')->robots('noindex, nofollow');

            return Inertia::render('auth/register', [
                'passwordRules' => Password::defaults()->toPasswordRulesString(),
            ]);
        });

        Fortify::twoFactorChallengeView(function () {
            Head::title('Two-factor authentication')->robots('noindex, nofollow');

            return Inertia::render('auth/two-factor-challenge');
        });

        Fortify::confirmPasswordView(function () {
            Head::title('Confirm password')->robots('noindex, nofollow');

            return Inertia::render('auth/confirm-password');
        });
    }

    /**
     * Configure rate limiting.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('passkeys', function (Request $request) {
            return Limit::perMinute(10)->by(
                ($request->input('credential.id') ?: $request->session()->getId()).'|'.$request->ip(),
            );
        });
    }
}
