<?php

namespace Tests\Feature;

use Tests\TestCase;

class RegularLoginErrorMessageTest extends TestCase
{
    public function test_regular_authentication_failure_does_not_advertise_authcentral(): void
    {
        config()->set('authentication.method', 'regular');
        config()->set('branding.theme_settings.show_auth_central_button', true);

        $response = $this->from('/login')->post('/regular-login', [
            'email' => 'missing-user@example.test',
            'password' => 'invalid-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertStringNotContainsString('AuthCentral', session('errors')->first('email'));
    }

    public function test_authcentral_failure_can_suggest_sso_when_button_is_visible(): void
    {
        config()->set('authentication.method', 'authcentral');
        config()->set('branding.theme_settings.show_auth_central_button', true);

        $this->from('/login')->post('/regular-login', [
            'email' => 'missing-user@example.test',
            'password' => 'invalid-password',
        ])->assertSessionHasErrors('email');

        $this->assertStringContainsString('AuthCentral', session('errors')->first('email'));
    }

    public function test_authcentral_failure_does_not_suggest_hidden_sso_option(): void
    {
        config()->set('authentication.method', 'authcentral');
        config()->set('branding.theme_settings.show_auth_central_button', false);

        $this->from('/login')->post('/regular-login', [
            'email' => 'missing-user@example.test',
            'password' => 'invalid-password',
        ])->assertSessionHasErrors('email');

        $this->assertStringNotContainsString('AuthCentral', session('errors')->first('email'));
    }
}
