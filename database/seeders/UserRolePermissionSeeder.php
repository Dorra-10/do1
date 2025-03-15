<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserRolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or Update Permissions (évite les doublons)
        Permission::firstOrCreate(['name' => 'view role']);
        Permission::firstOrCreate(['name' => 'create role']);
        Permission::firstOrCreate(['name' => 'update role']);
        Permission::firstOrCreate(['name' => 'delete role']);

        Permission::firstOrCreate(['name' => 'view permission']);
        Permission::firstOrCreate(['name' => 'create permission']);
        Permission::firstOrCreate(['name' => 'update permission']);
        Permission::firstOrCreate(['name' => 'delete permission']);

        Permission::firstOrCreate(['name' => 'view user']);
        Permission::firstOrCreate(['name' => 'create user']);
        Permission::firstOrCreate(['name' => 'update user']);
        Permission::firstOrCreate(['name' => 'delete user']);

        Permission::firstOrCreate(['name' => 'view project']); // Clé pour les employés
        Permission::firstOrCreate(['name' => 'create project']);
        Permission::firstOrCreate(['name' => 'update project']);
        Permission::firstOrCreate(['name' => 'delete project']);

        // Create or Update Roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $superviseurRole = Role::firstOrCreate(['name' => 'superviseur']);
        $employeeRole = Role::firstOrCreate(['name' => 'employee']);

        // Sync permissions to admin role (toutes les permissions)
        $allPermissionNames = Permission::pluck('name')->toArray();
        $adminRole->syncPermissions($allPermissionNames);

        // Sync permissions to superviseur role
        $superviseurRole->syncPermissions([
            'view project',
            'create project',
            'update project',
            'delete project',
            'view user',
        ]);

        // Sync permissions to employee role
        $employeeRole->syncPermissions([
            'view project', // Permission pour voir les projets
        ]);

    
    }
}