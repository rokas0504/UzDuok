<?php

namespace Database\Seeders;

use App\Models\Roles\Role;
use App\Models\Users\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get parent role
        $parentRole = Role::where('slug', 'parent')->first();

        $users = [
            [
                'email' => 'useris@gmail.com',
                'name' => 'Useris',
                'email_verified_at' => now(),
                'password' => config('admin.main_admin_password'),
                'role_id' => $parentRole->id,
            ],
        ];

        foreach ($users as $user) {
            User::factory()->create($user);
        }
    }
}
