<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

class ResetDemoCommand extends Command
{
    protected $signature = 'demo:reset {--force : Force the operation without confirmation}';

    protected $description = 'Reset the demo database and seed it with showcase data';

    public function handle(): int
    {
        if (App::environment('production') && ! $this->option('force')) {
            $this->error('This command cannot run in production without --force flag.');

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm('This will delete all data and reseed the database. Continue?')) {
            $this->info('Operation cancelled.');

            return self::SUCCESS;
        }

        $this->components->info('Resetting demo database...');

        $this->call('migrate:fresh', ['--force' => true]);

        $this->components->info('Seeding demo data...');

        $this->call('db:seed', [
            '--class' => 'Database\\Seeders\\DemoSeeder',
            '--force' => true,
        ]);

        $this->components->info('Demo database reset complete!');

        return self::SUCCESS;
    }
}
