<?php

use App\Models\User;
use Laravel\Fortify\Features;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

test('google redirect sends user to google', function () {
    Socialite::fake('google');

    $response = $this->get(route('auth.google.redirect'));

    $response->assertRedirect();
});

test('google callback creates a new user', function () {
    Socialite::fake('google', (new SocialiteUser)->map([
        'id' => 'google-123',
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
    ]));

    $response = $this->get(route('auth.google.callback'));

    $response->assertRedirect(route('dashboard'));

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'google_id' => 'google-123',
    ]);

    $user = User::where('email', 'jane@example.com')->first();
    expect($user->email_verified_at)->not->toBeNull();
});

test('google callback links existing email user', function () {
    $user = User::factory()->create(['email' => 'existing@example.com']);

    Socialite::fake('google', (new SocialiteUser)->map([
        'id' => 'google-456',
        'name' => 'Existing User',
        'email' => 'existing@example.com',
    ]));

    $response = $this->get(route('auth.google.callback'));

    $response->assertRedirect(route('dashboard'));

    $this->assertAuthenticated();

    $user->refresh();
    expect($user->google_id)->toBe('google-456');
});

test('google callback logs in existing google user', function () {
    $user = User::factory()->withGoogleId()->create([
        'google_id' => 'google-789',
    ]);

    Socialite::fake('google', (new SocialiteUser)->map([
        'id' => 'google-789',
        'name' => $user->name,
        'email' => $user->email,
    ]));

    $response = $this->get(route('auth.google.callback'));

    $response->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

test('google callback redirects to two-factor challenge when enabled', function () {
    if (! Features::canManageTwoFactorAuthentication()) {
        $this->markTestSkipped('Two-factor authentication is not enabled.');
    }

    $user = User::factory()->withTwoFactor()->withGoogleId()->create([
        'google_id' => 'google-2fa',
    ]);

    Socialite::fake('google', (new SocialiteUser)->map([
        'id' => 'google-2fa',
        'name' => $user->name,
        'email' => $user->email,
    ]));

    $response = $this->get(route('auth.google.callback'));

    $response->assertRedirect(route('two-factor.login'));

    $this->assertGuest();
    expect(session('login.id'))->toBe($user->id);
});

test('google routes are protected by guest middleware', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('auth.google.redirect'))
        ->assertRedirect(route('dashboard'));
});
