<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MakeAdmin extends Command
{
    protected $signature = 'make:admin
        {--name= : Admin name}
        {--email= : Admin email}
        {--password= : Admin password (will prompt if not provided)}';

    protected $description = 'Create an admin user for the CMS panel';

    public function handle(): int
    {
        $name = $this->option('name') ?: $this->ask('Name', 'Admin');
        $email = $this->option('email') ?: $this->ask('Email');

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email address.');
            return 1;
        }

        if (User::where('email', $email)->exists()) {
            $this->error("A user with email [{$email}] already exists.");
            return 1;
        }

        $password = $this->option('password');
        if (!$password) {
            $password = $this->secret('Password');
            $confirm = $this->secret('Confirm password');

            if ($password !== $confirm) {
                $this->error('Passwords do not match.');
                return 1;
            }
        }

        if (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters.');
            return 1;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ]);

        $this->newLine();
        $this->info("✅ Admin user created successfully!");
        $this->table(
            ['Field', 'Value'],
            [
                ['ID', $user->id],
                ['Name', $user->name],
                ['Email', $user->email],
                ['Login URL', route('login')],
            ]
        );

        return 0;
    }
}
