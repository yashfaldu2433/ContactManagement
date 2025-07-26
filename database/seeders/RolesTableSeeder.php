<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = config('constant.ROLES');

        foreach ($roles as $role) {
            // Find and create Role
            Role::query()->firstOrCreate([
                'name' => $role,
            ]);
        }
    }
}
