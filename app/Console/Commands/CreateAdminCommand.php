<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class CreateAdminCommand extends Command
{
    protected $signature = 'admin:create';

    protected $description = 'Create a new admin user';

    public function handle(): int
    {
        $username = text(
            label: 'Username',
            required: true,
            validate: fn(string $value) => Validator::make(
                ['username' => $value],
                ['username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')]],
            )->errors()->first('username'),
        );

        $password = password(
            label: 'Password',
            required: true,
        );

        password(
            label: 'Confirm password',
            required: true,
            validate: fn(string $value) => $value !== $password ? 'The passwords do not match.' : null,
        );

        $user = User::query()->create([
            'username' => $username,
            'password' => $password,
            'role' => UserRole::Admin,
        ]);

        $this->components->info("Admin account [{$user->username}] created successfully.");

        return self::SUCCESS;
    }
}
