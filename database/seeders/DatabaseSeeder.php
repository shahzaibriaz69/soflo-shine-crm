<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Roles and Permissions for Spatie
        $adminRole = Role::firstOrCreate(['name' => 'Super Admin']);
        $subAdminRole = Role::firstOrCreate(['name' => 'Sub Admin']);
        $staffRole = Role::firstOrCreate(['name' => 'Staff']);

        // Optional: Create basic permissions if needed
        $permission = Permission::firstOrCreate(['name' => 'manage quotes']);
        $adminRole->givePermissionTo($permission);

        // 2. Existing Seeders
        $this->call([
            CustomerSeeder::class,
            CustomerJobSeeder::class,
        ]);

        $this->call([
            OpportunitySeeder::class,
        ]);
    }
}