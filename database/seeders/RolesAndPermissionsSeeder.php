<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
                // Reset cached roles and permissions
                app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

                // Permissions
                $permissions = [
                    'create article report',
                    'view article report',
                    'update article report',
                    'delete article report',
                ];
        
                foreach ($permissions as $permission) {
                    Permission::firstOrCreate(['name' => $permission]);
                }
        
                // Roles and assign permissions
                $administrator = Role::firstOrCreate(['name' => 'administrator']);
                $executive = Role::firstOrCreate(['name' => 'executive']);
                $editor = Role::firstOrCreate(['name' => 'editor']);
        
                // Assigning permissions to roles
                $administrator->syncPermissions($permissions);
                $executive->syncPermissions(['view article report']);
                $editor->syncPermissions(['create article report', 'view article report', 'update article report']);
    }
}
