<?php

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\RelationManagers\RolesRelationManager;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('can list users', function () {
    $users = User::factory()->count(3)->create();

    Livewire::test(ListUsers::class)
        ->assertOk()
        ->assertCanSeeTableRecords($users);
});

it('can load the create page', function () {
    Livewire::test(CreateUser::class)
        ->assertOk();
});

it('can create a user', function () {
    $newUser = User::factory()->make();

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => $newUser->name,
            'email' => $newUser->email,
            'password' => 'password',
            'active' => true,
        ])
        ->call('create')
        ->assertNotified()
        ->assertRedirect();

    $this->assertDatabaseHas('users', [
        'name' => $newUser->name,
        'email' => $newUser->email,
        'active' => true,
    ]);
});

it('can load the edit page', function () {
    $user = User::factory()->create();

    Livewire::test(EditUser::class, [
        'record' => $user->id,
    ])
        ->assertOk();
});

it('can update a user', function () {
    $user = User::factory()->create();
    $newData = User::factory()->make();

    Livewire::test(EditUser::class, [
        'record' => $user->id,
    ])
        ->fillForm([
            'name' => $newData->name,
            'email' => $newData->email,
        ])
        ->call('save')
        ->assertNotified();

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => $newData->name,
        'email' => $newData->email,
    ]);
});

it('can render the roles relation manager', function () {
    $user = User::factory()->create();

    Livewire::test(RolesRelationManager::class, [
        'ownerRecord' => $user,
        'pageClass' => EditUser::class,
    ])
        ->assertOk();
});

it('denies access to non-admin users', function () {
    $regularUser = User::factory()->create();

    $this->actingAs($regularUser)
        ->get(route('filament.admin.resources.users.index'))
        ->assertForbidden();
});
