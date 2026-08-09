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
            'view users', 'manage users', 'manage pins', 'approve payments',
            'process commissions', 'approve withdrawals', 'manage tickets', 'review kyc',
            'view reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $agent = Role::firstOrCreate(['name' => 'agent']);
        $member = Role::firstOrCreate(['name' => 'member']);

        $admin->syncPermissions($permissions);
        $agent->syncPermissions(['manage pins', 'approve payments']);
        $member->syncPermissions([]);
    }
}
