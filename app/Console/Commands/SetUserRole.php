<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class SetUserRole extends Command
{
    protected $signature = 'user:role
        {email : User email}
        {role : Role to assign (admin or editor)}';

    protected $description = 'Set or change a user\'s role (admin/editor)';

    public function handle(): int
    {
        $email = $this->argument('email');
        $role = strtolower($this->argument('role'));

        if (!in_array($role, ['admin', 'editor'], true)) {
            $this->error('Role must be either "admin" or "editor".');
            return 1;
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email [{$email}] not found.");
            return 1;
        }

        $oldRole = $user->role ?? 'editor';
        $user->role = $role;
        $user->save();

        $this->info("✅ Role updated: {$user->name} ({$email}): {$oldRole} → {$role}");

        return 0;
    }
}
