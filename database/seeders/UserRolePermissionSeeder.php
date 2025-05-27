<?php

namespace  Database\Seeders;

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
    public function run()
    {
        // Permissions
        $permissions = [
            'view role',
            'create role',
            'edit role',
            'delete role',
            'view permission',
            'create permission',
            'edit permission',
            'delete permission',
            'view user',
            'create user',
            'update user',
            'delete user',
            'view project',
            'create project',
            'update project',
            'delete project',
            'view document',
            'upload document',
            'update document',
            'delete document',
            'view access',
            'give access',
            'update access',
            'delete access',
            // ajoute toutes les permissions nécessaires ici...
        ];

        // Création des permissions
        foreach ($permissions as $permission) {
            // Utilise firstOrCreate pour éviter les doublons
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Création des rôles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $supervisorRole = Role::firstOrCreate(['name' => 'supervisor']);
        $employeeRole = Role::firstOrCreate(['name' => 'employee']);

        // Attribuer toutes les permissions au rôle admin
        $allPermissionNames = Permission::pluck('name')->toArray();
        $adminRole->syncPermissions($allPermissionNames);

        // Synchronisation des permissions au rôle superviseur
        $supervisorRole->syncPermissions([
            'view project',
            'create project',
            'update project',
            'delete project',
            'view user',
            'view document',
            'upload document',
            'update document',
        ]);

        // Let's Create User and assign Role to it.
        $adminUser = User::firstOrCreate([
                            'email' => 'admin@gmail.com'
                        ], [
                            'name' => 'Admin',
                            'email' => 'admin@gmail.com',
                            'password' => Hash::make ('12345678'),
                        ]);

        $adminUser->assignRole($adminRole);
        $supervisorUser = User::firstOrCreate([
            'email' => 'superviseur@gmail.com'
        ], [
            'name' => 'superviseur',
            'email' => 'superviseur@gmail.com',
            'password' => Hash::make ('12345678'),
        ]);

       $supervisorUser->assignRole($supervisorRole);


        $employeeUser = User::firstOrCreate([
                            'email' => 'employee@gmail.com',
                        ], [
                            'name' => 'employee',
                            'email' => 'employee@gmail.com',
                            'password' => Hash::make('12345678'),
                        ]);

        $employeeUser->assignRole($employeeRole);
    }
}