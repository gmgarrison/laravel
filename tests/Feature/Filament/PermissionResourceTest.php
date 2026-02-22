<?php

use App\Filament\Resources\Permissions\Pages\ManagePermissions;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('can list permissions', function () {
    Livewire::test(ManagePermissions::class)
        ->assertOk();
});

it('can create a permission', function () {
    Livewire::test(ManagePermissions::class)
        ->callAction('create', [
            'name' => 'manage_settings',
            'guard_name' => 'web',
        ])
        ->assertNotified();

    $this->assertDatabaseHas('permissions', [
        'name' => 'manage_settings',
        'guard_name' => 'web',
    ]);
});
