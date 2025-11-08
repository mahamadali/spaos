<?php

namespace Database\Seeders\Auth;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Class PermissionRoleTableSeeder.
 */

class PermissionRoleTableSeeder extends Seeder
{
    /**
     * Run the database seed.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        $super_admin = Role::firstOrCreate(['name' => 'super admin', 'title' => 'Super Admin', 'is_fixed' => true]);
        $admin = Role::firstOrCreate(['name' => 'admin', 'title' => 'Admin', 'is_fixed' => true]);
        $manager = Role::firstOrCreate(['name' => 'manager', 'title' => 'manager', 'is_fixed' => true]);
        $employee = Role::firstOrCreate(['name' => 'employee', 'title' => 'employee', 'is_fixed' => true]);
        $user = Role::firstOrCreate(['name' => 'user', 'title' => 'user', 'is_fixed' => true]);

        $modules = config('constant.MODULES');

        $new_permissions = [
            'view_expired_subscriptions',
            'view_pending_subscriptions',
            'manage_payments',
            'approve_payment',
        ];

        foreach ($modules as $key => $module) {
            $permissions = ['view', 'add', 'edit', 'delete'];
            $module_name = strtolower(str_replace(' ', '_', $module['module_name']));
            foreach ($permissions as $key => $value) {
                $permission_name = $value . '_' . $module_name;
                Permission::firstOrCreate(['name' => $permission_name, 'is_fixed' => true]);
            }
            if (isset($module['more_permission']) && is_array($module['more_permission'])) {
                foreach ($module['more_permission'] as $key => $value) {
                    $permission_name = $module_name . '_' . $value;
                    Permission::firstOrCreate(['name' => $permission_name, 'is_fixed' => true]);
                }
            }
        }

        foreach ($new_permissions as $value) {
            Permission::firstOrCreate(['name' => $value, 'is_fixed' => true]);
        }

        $super_admin_permissions = Permission::get()->pluck('name')->toArray(); // Get all permissions

        $admin_permissions = array_diff($super_admin_permissions, [
            'add_subscription',
            'edit_subscription',
            'view_subscription',
            'delete_subscription',
            'approve_payment',
            'add_plan',
            'edit_plan',
            'view_plan',
            'delete_plan',
            'setting_intigrations',
            'plan_tableview',
            'subscription_tableview',
            'view_pending_subscriptions',
            'view_expired_subscriptions',
            'manage_payments',
            'view_app_banner',
            'add_app_banner',
            'edit_app_banner',
            'delete_app_banner',
            'view_role_permissions',
            'setting_invoice',
            'setting_custom_code',
            'setting_notification',
            'setting_menu_builder',
            'setting_language',
           
           
        ]); // Remove the specified permissions

        $super_admin_permissions = array_diff($super_admin_permissions, [
            'setting_quick_booking',
            'setting_invoice',
            'setting_custom_code',
            'setting_notification',
            'setting_commission',
            'setting_holiday',
            'setting_bussiness_hours',
            'setting_menu_builder',
            
        ]);

        $super_admin->givePermissionTo($super_admin_permissions);


        $admin->givePermissionTo($admin_permissions);

        $manager->givePermissionTo([
            'view_booking',
            'add_booking',
            'edit_booking',
            'delete_booking',
            'menu_builder_header',
            'view_service',
            'add_service',
            'edit_service',
            'delete_service',
            'service_gallery',
            'view_staff',
            'add_staff',
            'edit_staff',
            'delete_staff',
            'view_customer',
            'add_customer',
            'edit_customer',
            'delete_customer',
            'setting_commission',
            'view_commission',
            'add_commission',
            'edit_commission',
            'delete_commission',
        ]);

        Schema::enableForeignKeyConstraints();
    }
}
