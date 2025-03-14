<?php

namespace Database\Seeders;

use  App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserRolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Permissions
        Permission::create(['name' => 'view role']);
        Permission::create(['name' => 'create role']);
        Permission::create(['name' => 'update role']);
        Permission::create(['name' => 'delete role']);

        Permission::create(['name' => 'view permission']);
        Permission::create(['name' => 'create permission']);
        Permission::create(['name' => 'update permission']);
        Permission::create(['name' => 'delete permission']);

        Permission::create(['name' => 'view user']);
        Permission::create(['name' => 'create user']);
        Permission::create(['name' => 'update user']);
        Permission::create(['name' => 'delete user']);

        Permission::create(['name' => 'create project']);
        Permission::create(['name' => 'update project']);
        Permission::create(['name' => 'delete project']);
    
        // Create Roles
        $adminRole = Role::create(['name' => 'admin']); //as admin
        $superviseurRole = Role::create(['name' => 'superviseur']);
        $employeeRole = Role::create(['name' => 'employee']);
        

        // Lets give all permission to admin role.
        $allPermissionNames = Permission::pluck('name')->toArray();

        $adminRole->givePermissionTo($allPermissionNames);

        // Let's give few permissions to superviseur role.
        $superviseurRole->givePermissionTo(['create project', 'view project', 'update project','delete project']);
        $employeeRole->givePermissionTo(['create project', 'view project', 'update project','delete project']);
        
    
        // Let's Create User and assign Role to it.


    }
}