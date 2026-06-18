<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite doesn't support changing column types easily.
        // We need to recreate the table to change `id` from auto-increment to string (uuid).
        // Since this is a dev environment with SQLite, we'll drop and recreate.

        Schema::disableForeignKeyConstraints();

        // Get existing user data before dropping
        $users = DB::table('users')->get();

        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('role')->default('editor');
            $table->string('phone', 20)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        // Re-insert existing users with a UUID
        foreach ($users as $user) {
            DB::table('users')->insert([
                'id' => (string) Str::uuid(),
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role ?? 'editor',
                'phone' => $user->phone ?? null,
                'email_verified_at' => $user->email_verified_at,
                'password' => $user->password,
                'remember_token' => $user->remember_token,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]);
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // Cannot reliably restore auto-increment IDs from UUIDs.
        // This migration is one-way in dev context.
        Schema::disableForeignKeyConstraints();

        $users = DB::table('users')->get();

        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('role')->default('editor');
            $table->string('phone', 20)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        $i = 1;
        foreach ($users as $user) {
            DB::table('users')->insert([
                'id' => $i++,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role ?? 'editor',
                'phone' => $user->phone ?? null,
                'email_verified_at' => $user->email_verified_at,
                'password' => $user->password,
                'remember_token' => $user->remember_token,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]);
        }

        Schema::enableForeignKeyConstraints();
    }
};
