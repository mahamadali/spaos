<?php

namespace Modules\NotificationTemplate\database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Constant\Models\Constant;
use Modules\NotificationTemplate\Models\NotificationTemplate;
use App\Models\User;
use Modules\NotificationTemplate\Trait\NotificationTemplateTrait;

class NotificationTemplateSeeder extends Seeder
{
    use NotificationTemplateTrait;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Disable foreign key checks!
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        /*
         * NotificationTemplates Seed
         * ------------------
         */

        // DB::table('notificationtemplates')->truncate();

        $types = [
            // Vendor Registration Notification Types
            [
                'type' => 'notification_type',
                'value' => 'vendor_registered',
                'name' => 'Vendor Registered',
            ],
            [
                'type' => 'notification_param_button',
                'value' => 'vendor_email',
                'name' => 'Vendor Email',
            ],
            [
                'type' => 'notification_param_button',
                'value' => 'registration_date',
                'name' => 'Registration Date',
            ],
            // Purchase Plan Notification Types
            [
                'type' => 'notification_type',
                'value' => 'purchase_plan',
                'name' => 'Purchase Plan',
            ],
            [
                'type' => 'notification_param_button',
                'value' => 'plan_name',
                'name' => 'Plan Name',
            ],
            [
                'type' => 'notification_param_button',
                'value' => 'plan_start_date',
                'name' => 'Plan Start Date',
            ],
            [
                'type' => 'notification_param_button',
                'value' => 'plan_expiry_date',
                'name' => 'Plan Expiry Date',
            ],
            [
                'type' => 'notification_type',
                'value' => 'new_booking',
                'name' => 'New Booking',
            ],
            [
                'type' => 'notification_type',
                'value' => 'check_in_booking',
                'name' => 'Check-In On Booking',
            ],
            [
                'type' => 'notification_type',
                'value' => 'checkout_booking',
                'name' => 'Checkout On Booking',
            ],
            [
                'type' => 'notification_type',
                'value' => 'complete_booking',
                'name' => 'Complete On Booking',
            ],
            [
                'type' => 'notification_type',
                'value' => 'cancel_booking',
                'name' => 'Cancel On Booking',
            ],
            [
                'type' => 'notification_type',
                'value' => 'quick_booking',
                'name' => 'Quick Booking',
            ],
            [
                'type' => 'notification_type',
                'value' => 'change_password',
                'name' => 'Change Password',
            ],
            [
                'type' => 'notification_type',
                'value' => 'forget_email_password',
                'name' => 'Forget Email/Password',
            ],

            [
                'type' => 'notification_param_button',
                'value' => 'id',
                'name' => 'ID',
            ],
            [
                'type' => 'notification_param_button',
                'value' => 'vendor_name',
                'name' => 'Vendor Name',
            ],
            [
                'type' => 'notification_param_button',
                'value' => 'description',
                'name' => 'Description / Note',
            ],
            [
                'type' => 'notification_param_button',
                'value' => 'booking_id',
                'name' => 'Booking ID',
            ],
            [
                'type' => 'notification_param_button',
                'value' => 'booking_date',
                'name' => 'Booking Date',
            ],
            [
                'type' => 'notification_param_button',
                'value' => 'booking_time',
                'name' => 'Booking Time',
            ],
            [
                'type' => 'notification_param_button',
                'value' => 'service_name',
                'name' => 'Booking Services Names',
            ],
            [
                'type' => 'notification_param_button',
                'value' => 'booking_duration',
                'name' => 'Booking Duration',
            ],
            [
                'type' => 'notification_param_button',
                'value' => 'employee_name',
                'name' => 'Staff Name',
            ],
            [
                'type' => 'notification_param_button',
                'value' => 'venue_address',
                'name' => 'Venue / Address',
            ],
            [
                'type' => 'notification_param_button',
                'value' => 'logged_in_user_fullname',
                'name' => 'Your Name',
            ],
            [
                'type' => 'notification_param_button',
                'value' => 'logged_in_user_role',
                'name' => 'Your Position',
            ],
            [
                'type' => 'notification_param_button',
                'value' => 'company_name',
                'name' => 'Company Name',
            ],
            [
                'type' => 'notification_param_button',
                'value' => 'company_contact_info',
                'name' => 'Company Info',
            ],
            [
                'type' => 'notification_param_button',
                'value' => 'vendor_id',
                'name' => 'Vendor\' ID',
            ],
            [
                'type' => 'notification_param_button',
                'value' => 'vendor_password',
                'name' => 'Vendor Password',
            ],
            [
                'type' => 'notification_param_button',
                'value' => 'link',
                'name' => 'Link',
            ],
            [
                'type' => 'notification_param_button',
                'value' => 'check_out_time',
                'name' => 'Check-out Time',
            ],
            [
                'type' => 'notification_param_button',
                'value' => 'site_url',
                'name' => 'Site URL',
            ],
            [
                'type' => 'notification_type',
                'value' => 'new_subscription',
                'name' => 'New Subscription',
            ],
            [
                'type' => 'notification_type',
                'value' => 'cancel_subscription',
                'name' => 'Cancel Subscription',
            ],
            [
                'type' => 'notification_to',
                'value' => 'user',
                'name' => 'User',
            ],

            [
                'type' => 'notification_to',
                'value' => 'admin',
                'name' => 'Admin',
            ],
            [
                'type' => 'notification_to',
                'value' => 'super admin',
                'name' => 'Super Admin',
            ],
        ];

        foreach ($types as $value) {
            Constant::updateOrCreate(['type' => $value['type'], 'value' => $value['value']], $value);
        }

        $adminUsers = User::whereIn('user_type', ['super admin', 'admin'])->get();

        // Enable foreign key checks!
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('notification_templates')->delete();
        DB::table('notification_template_content_mapping')->delete();

        $adminUsers->map(function ($admin) {
            $this->addNotificationTemplate($admin->id);
        });
    }
}
