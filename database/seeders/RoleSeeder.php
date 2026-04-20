<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'home.view',
            'dashboard.view',
            'info.view',
            'info.faculties.view',
            'info.curricula.view',
            'info.specialties.view',
            'info.groups.view',
            'lessons.view',
            'lessons.create',
            'lessons.update',
            'lessons.delete',
            'lessons.checking',
            'reports.view',
            'archives.view',
            'users.view',
            'users.edit',
            'accounts.view',
            'accounts.create',
            'accounts.edit',
            'accounts.delete',
            'system.options.view',
            'system.options.update',
            'options.view',
            'options.update',
        ];
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }
        $desc = [
            'super_admin' => 'Super admin',
            'admin' => 'Administrator',
            'moder' => 'Tekshiruvchi',
            'uploader' => 'Yuklovchi',
            'user' => 'Foydalanuvchi',
        ];

        $roles = [
            'super_admin' => $permissions,
            'admin' => $permissions,
            'moder' => [
                'home.view',
                'dashboard.view',
                'lessons.view',
                'lessons.create',
                'lessons.update',
                'lessons.delete',
                'lessons.checking',
                'reports.view',
                'archives.view',
                'accounts.view',
                'accounts.create',
                'accounts.edit',
                'accounts.delete',
                'options.view',
                'options.update',
            ],
            'uploader' => [
                'home.view',
                'dashboard.view',
                'lessons.view',
                'lessons.create',
                'lessons.update',
                'reports.view',
                'archives.view',
                'accounts.view',
                'accounts.create',
                'accounts.edit',
                'options.view',
                'options.update',
            ],
            'user' => [
                'home.view',
                'options.view',
                'options.update',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => 'web'],
                ['desc' => $desc[$roleName]]
            );
            $role->syncPermissions($rolePermissions);
        }
    }
}
