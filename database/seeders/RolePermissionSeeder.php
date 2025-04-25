<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $super_admin = Role::create(['name' => 'super_admin']);
        $account_manager = Role::create(['name' => 'account_manager']);
        $chef = Role::create(['name' => 'chef']);

        $all_permissions = $this->create_permissions(['user','role_permission','item','modifier','table', 'section','tax','fee','order','customer','kot']);


        $account_manager->syncPermissions($all_permissions);
        $this->revoke_permissions($account_manager, 'role_permission');
        $this->revoke_permissions($account_manager, 'user');
        $chef->syncPermissions(['view_kot','add_edit_kot']);
    }


    // extra function to simplify permissions

    private function create_permissions(array $permissions)
    {
        $createdPermissions = [];

        foreach ($permissions as $permission) {
            $createdPermissions[] = Permission::create(['name' => 'view_'.$permission]);
            $createdPermissions[] = Permission::create(['name' => 'add_edit_'.$permission]);
            $createdPermissions[] = Permission::create(['name' => 'delete_'.$permission]);
        }

        return $createdPermissions;
    }


    private function revoke_permissions($role, $permission)
    {
        $role->revokePermissionTo(['view_'.$permission,'add_edit_'.$permission,'delete_'.$permission]);
    }
}
