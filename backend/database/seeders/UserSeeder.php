<?php

namespace Database\Seeders;

use App\Models\Users\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'email' => 'dev@starkodas.lt',
                'name' => 'Starkodas',
                'email_verified_at' => now(),
                'password' => config('admin.main_admin_password'),
            ],
        ];

        foreach ($users as $user) {
            User::factory()->create($user);
        }
    }
}
