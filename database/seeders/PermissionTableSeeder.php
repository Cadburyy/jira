<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionTableSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
           'role-list',
           'role-create',
           'role-edit',
           'role-delete',
           'dandory-list',
           'dandory-create',
           'dandory-edit',
           'dandory-delete'
        ];

        foreach ($permissions as $permission) {
             Permission::firstOrCreate(['name' => $permission]);
        }

        $roles = [
            'Admin',
            'Teknisi',
            'Views',
            'AdminTeknisi',
            'Requestor'
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole) {
            $adminRole->syncPermissions(Permission::pluck('name'));
        }

        $teknisiRole = Role::where('name', 'Teknisi')->first();
        if ($teknisiRole) {
            $teknisiRole->givePermissionTo('dandory-list');
        }

        $viewsRole = Role::where('name', 'Views')->first();
        if ($viewsRole) {
            $viewsRole->givePermissionTo('dandory-list');
        }

        $adminTeknisiRole = Role::where('name', 'AdminTeknisi')->first();
        if ($adminTeknisiRole) {
            $adminTeknisiRole->givePermissionTo([
                'dandory-list',
                'dandory-create',
                'dandory-edit'
            ]);
        }

        $requestorRole = Role::where('name', 'Requestor')->first();
        if ($requestorRole) {
            $requestorRole->givePermissionTo([
                'dandory-list',
                'dandory-create'
            ]);
        }
    }
}
