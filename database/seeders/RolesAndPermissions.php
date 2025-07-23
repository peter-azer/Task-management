<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
class RolesAndPermissions extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $memberRole = Role::firstOrCreate(['name' => 'member']);
        $observerRole = Role::firstOrCreate(['name' => 'observer']);

        $permissions = [
            
            // user management
            'manage-users',
            'create-user',
            'edit-user',
            'delete-user',
            'view-user',
            'assign-role',
            'assign-permission',
            'view-users',

            // team management
            'manage-permissions',
            'create-team',
            'edit-team',
            'delete-team',
            'send-invitation',
            'manage-settings',

            // project management
            'manage-projects',
            'create-project',
            'edit-project',
            'delete-project',
            'view-projects',
            // task management
            'manage-tasks',
            'create-task',
            'edit-task',
            'delete-task',
            'view-tasks',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign permissions to roles
        $superAdminRole->syncPermissions(Permission::all());
        $adminRole->syncPermissions([
            // team management
            'manage-permissions',
            'create-team',
            'edit-team',
            'delete-team',
            'send-invitation',
            'manage-settings',
            // project management
            'manage-projects',
            'create-project',
            'edit-project',
            'delete-project',
            'view-projects',
            // task management
            'manage-tasks',
            'create-task',
            'edit-task',
            'delete-task',
            'view-tasks',
        ]);

        $superAdmin = User::firstOrCreate([
            'name' => 'Super Admin',
            'email' => 'super_admin@taskly.com',
            'password' => Hash::make('adminadmin'),
            'is_active' => true,
        ]);
        $superAdmin->assignRole('super-admin');
    }
}
