<?php

use App\Models\User;

it('displays the welcome page for guests', function () {
    $this->get('/')
        ->assertSuccessful()
        ->assertSee('Welcome to your application')
        ->assertSee('Log in')
        ->assertSee('Register');
});

it('displays the welcome page for authenticated users', function () {
    $this->actingAs(User::factory()->create())
        ->get('/')
        ->assertSuccessful()
        ->assertSee('Dashboard')
        ->assertDontSee('Log in');
});
