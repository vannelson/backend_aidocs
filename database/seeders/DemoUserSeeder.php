<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Ava Owner', 'email' => 'ava@gooddocs.test'],
            ['name' => 'Ben Editor', 'email' => 'ben@gooddocs.test'],
            ['name' => 'Cara Viewer', 'email' => 'cara@gooddocs.test'],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
