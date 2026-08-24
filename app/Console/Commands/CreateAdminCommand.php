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
    protected $signature = 'admin:create
                            {username? : The username of the new admin}
                            {--password= : The password of the new admin}';

    protected $description = 'Create a new admin user';

    public function handle(): int
    {
        $username = $this->argument('username');
        $password = $this->option('password');

        if ($username === null) {
            if (! $this->isInteractive()) {
                $this->components->error('The username argument is required when running without an interactive terminal.');

                return self::FAILURE;
            }

            $username = text(
                label: 'Username',
                required: true,
                validate: fn (string $value): ?string => $this->validateUsername($value),
            );
        }

        if ($password === null) {
            if (! $this->isInteractive()) {
                $this->components->error('The --password option is required when running without an interactive terminal.');

                return self::FAILURE;
            }

            $password = password(
                label: 'Password',
                required: true,
            );

            password(
                label: 'Confirm password',
                required: true,
                validate: fn (string $value): ?string => $value !== $password ? 'The passwords do not match.' : null,
            );
        }

        if ($error = $this->validateUsername($username)) {
            $this->components->error($error);

            return self::FAILURE;
        }

        if ($password === '') {
            $this->components->error('The password must not be empty.');

            return self::FAILURE;
        }

        $user = User::query()->create([
            'username' => $username,
            'password' => $password,
            'role' => UserRole::Admin,
        ]);

        $this->components->info("Admin account [{$user->username}] created successfully.");

        return self::SUCCESS;
    }

    private function validateUsername(string $username): ?string
    {
        return Validator::make(
            ['username' => $username],
            ['username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')]],
        )->errors()->first('username') ?: null;
    }

    /**
     * Laravel Prompts needs a real terminal; piped or `--no-interaction` runs have to use arguments.
     */
    private function isInteractive(): bool
    {
        return $this->input->isInteractive() && stream_isatty(STDIN);
    }
}
