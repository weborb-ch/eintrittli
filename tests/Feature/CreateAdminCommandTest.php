<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('creates an admin from arguments without a terminal', function () {
    $this->artisan('admin:create', [
        'username' => 'testadmin',
        '--password' => 'testadmin',
    ])->assertSuccessful();

    $user = User::where('username', 'testadmin')->sole();

    expect($user->role)->toBe(UserRole::Admin)
        ->and(Hash::check('testadmin', $user->password))->toBeTrue();
});

it('fails with a readable message when the username is missing', function () {
    $this->artisan('admin:create', ['--password' => 'secret'])
        ->expectsOutputToContain('The username argument is required when running without an interactive terminal.')
        ->assertFailed();

    expect(User::count())->toBe(0);
});

it('fails with a readable message when the password is missing', function () {
    $this->artisan('admin:create', ['username' => 'testadmin'])
        ->expectsOutputToContain('The --password option is required when running without an interactive terminal.')
        ->assertFailed();

    expect(User::count())->toBe(0);
});

it('refuses to create a duplicate username', function () {
    User::factory()->create(['username' => 'testadmin', 'role' => UserRole::Admin]);

    $this->artisan('admin:create', [
        'username' => 'testadmin',
        '--password' => 'testadmin',
    ])->assertFailed();

    expect(User::where('username', 'testadmin')->count())->toBe(1);
});

it('refuses an empty password', function () {
    $this->artisan('admin:create', [
        'username' => 'testadmin',
        '--password' => '',
    ])
        ->expectsOutputToContain('The password must not be empty.')
        ->assertFailed();

    expect(User::count())->toBe(0);
});
