<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define Roles as per Section 8 implementation guide
        $roles = [
            'Owner/Admin',
            'Office/Dispatcher',
            'Estimator/Sales',
            'Technician/Crew',
            'Bookkeeper',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        $this->command?->info('Default roles created successfully!');
    }
}