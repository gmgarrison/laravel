<?php

use App\Filament\Resources\Roles\Pages\ManageRoles;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('can list roles', function () {
    Livewire::test(ManageRoles::class)
        ->assertOk();
});

it('can create a role', function () {
    Livewire::test(ManageRoles::class)
        ->callAction('create', [
            'name' => 'editor',
            'guard_name' => 'web',
        ])
        ->assertNotified();

    $this->assertDatabaseHas('roles', [
        'name' => 'editor',
        'guard_name' => 'web',
    ]);
});
