<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Fortify::ignoreRoutes();

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        // Custom authentication views that won't be overwritten by Laravel updates
        Fortify::loginView(function () {
            return view('custom-auth.login');
        });

        Fortify::registerView(function () {
            return view('custom-auth.register', ['userType' => 'staff']);
        });

        Fortify::requestPasswordResetLinkView(function () {
            return view('custom-auth.forgot-password');
        });

        Fortify::resetPasswordView(function ($request) {
            return view('custom-auth.reset-password', ['request' => $request]);
        });

        Fortify::confirmPasswordView(function () {
            return view('custom-auth.confirm-password');
        });

        Fortify::verifyEmailView(function () {
            return view('custom-auth.verify-email');
        });

        Fortify::twoFactorChallengeView(function () {
            return view('custom-auth.two-factor-challenge');
        });
    }
}
