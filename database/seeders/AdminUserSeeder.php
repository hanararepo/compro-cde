<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed initial users for Administrator, Editor, and Author roles.
     */
    public function run(): void
    {
        // 1. Administrator (Super Admin)
        $admin = User::firstOrCreate(
            ['email' => 'admin@cms.local'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles(['Administrator']);

        // 2. Admin (Full CMS access except Roles & ACL)
        $staffAdmin = User::firstOrCreate(
            ['email' => 'staffadmin@cms.local'],
            [
                'name' => 'CMS Admin',
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $staffAdmin->syncRoles(['Admin']);

        // 2. Editor
        $editor = User::firstOrCreate(
            ['email' => 'editor@cms.local'],
            [
                'name' => 'Editorial Lead',
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $editor->assignRole('Editor');

        // 3. Author / Writer
        $author = User::firstOrCreate(
            ['email' => 'author@cms.local'],
            [
                'name' => 'Content Writer',
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $author->assignRole('Author');
    }
}
