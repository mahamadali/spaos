<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\MenuBuilder\Models\MenuBuilder;
use Modules\Subscriptions\Models\Plan;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plan = Plan::where('is_free_plan', 1)->first() ??  new Plan();
        $permission_ids = MenuBuilder::whereNull('parent_id')->pluck('id')->toArray();

        // Handle the other plans
        $plans = [
            [
                'name' => 'Free',
                'type' => 'Weekly',
                'duration' => 1,
                'identifier' => 'free',
                'price' => 0.00,
                'total_price' => 0.00,
                'tax' => 0.00,
                'currency' => 'inr',
                'permission_ids' => json_encode(explode(',', 'view_branch,add_branch,edit_branch,delete_branch,branch_gallery,view_dashboard,add_dashboard,edit_dashboard,delete_dashboard,view_booking,add_booking,edit_booking,delete_booking,booking_booking_tableview,view_service,add_service,edit_service,delete_service,service_gallery,view_category,add_category,edit_category,delete_category,view_package,add_package,edit_package,delete_package,view_subcategory,add_subcategory,edit_subcategory,delete_subcategory,view_promotion,add_promotion,edit_promotion,delete_promotion,view_staff,add_staff,edit_staff,delete_staff,staff_password,view_customer,add_customer,edit_customer,delete_customer,customer_password,view_page,add_page,edit_page,delete_page,view_tax,add_tax,edit_tax,delete_tax,view_review,add_review,edit_review,delete_review,view_setting,setting_general,setting_misc,setting_quick_booking,setting_customization,setting_mail,setting_currency,setting_commission,setting_holiday,setting_bussiness_hours,setting_language,view_staff_earning,add_staff_earning,edit_staff_earning,delete_staff_earning,add_report,edit_report,delete_report,view_staff_service,add_staff_service,edit_staff_service,delete_staff_service,view_location,add_location,edit_location,delete_location,view_report,view_staff_payout,add_staff_payout,edit_staff_payout,delete_staff_payout,view_notification_list,add_notification_list,edit_notification_list,delete_notification_list,view_notification_template,add_notification_template,edit_notification_template,delete_notification_template,view_app_banner,add_app_banner,edit_app_banner,delete_app_banner,view_access_control,add_access_control,edit_access_control,delete_access_control,view_product,add_product,edit_product,delete_product,view_product_variations,add_product_variations,edit_product_variations,delete_product_variations,view_product_orders,add_product_orders,edit_product_orders,delete_product_orders,view_logistics,add_logistics,edit_logistics,delete_logistics,view_shipping_zone,add_shipping_zone,edit_shipping_zone,delete_shipping_zone,view_user_inquiry,add_user_inquiry,edit_user_inquiry,delete_user_inquiry')),
                'description' => 'Best for individuals or small businesses exploring the platform with limited access.',
                'status' => 1,
                'max_appointment' => 1,
                'max_branch' => 1,
                'max_service' => 1,
                'max_staff' => 1,
                'max_customer' => 1,
                'created_by' => 1,
                'updated_by' => 1,
                'is_free_plan' => 1,
            ],
            [
                'name' => 'Basic',
                'type' => 'Monthly',
                'duration' => 1,
                'identifier' => 'basic',
                'price' => 50.00,
                'total_price' => 50.00,
                'tax' => 0.00,
                'currency' => 'inr',
                'permission_ids' => json_encode(explode(',', 'view_branch,add_branch,edit_branch,delete_branch,branch_gallery,view_dashboard,add_dashboard,edit_dashboard,delete_dashboard,view_booking,add_booking,edit_booking,delete_booking,booking_booking_tableview,view_service,add_service,edit_service,delete_service,service_gallery,view_category,add_category,edit_category,delete_category,view_package,add_package,edit_package,delete_package,view_subcategory,add_subcategory,edit_subcategory,delete_subcategory,view_promotion,add_promotion,edit_promotion,delete_promotion,view_staff,add_staff,edit_staff,delete_staff,staff_password,view_customer,add_customer,edit_customer,delete_customer,customer_password,view_page,add_page,edit_page,delete_page,view_tax,add_tax,edit_tax,delete_tax,view_review,add_review,edit_review,delete_review,view_setting,setting_general,setting_misc,setting_quick_booking,setting_customization,setting_mail,setting_currency,setting_commission,setting_holiday,setting_bussiness_hours,setting_language,view_staff_earning,add_staff_earning,edit_staff_earning,delete_staff_earning,add_report,edit_report,delete_report,view_staff_service,add_staff_service,edit_staff_service,delete_staff_service,view_location,add_location,edit_location,delete_location,view_report,view_staff_payout,add_staff_payout,edit_staff_payout,delete_staff_payout,view_notification_list,add_notification_list,edit_notification_list,delete_notification_list,view_notification_template,add_notification_template,edit_notification_template,delete_notification_template,view_app_banner,add_app_banner,edit_app_banner,delete_app_banner,view_access_control,add_access_control,edit_access_control,delete_access_control,view_product,add_product,edit_product,delete_product,view_product_variations,add_product_variations,edit_product_variations,delete_product_variations,view_product_orders,add_product_orders,edit_product_orders,delete_product_orders,view_logistics,add_logistics,edit_logistics,delete_logistics,view_shipping_zone,add_shipping_zone,edit_shipping_zone,delete_shipping_zone,view_faq,add_faq,edit_faq,delete_faq,view_blog,add_blog,edit_blog,delete_blog,view_user_inquiry,add_user_inquiry,edit_user_inquiry,delete_user_inquiry')),
                'description' => 'Ideal for small businesses managing a single branch with a limited number of services and staff.',
                'status' => 1,
                'max_appointment' => 5,
                'max_branch' => 1,
                'max_service' => 2,
                'max_staff' => 2,
                'max_customer' => 2,
                'created_by' => 1,
                'updated_by' => 1,
                'is_free_plan' => 0,
            ],
            [
                'name' => 'Premium',
                'type' => 'Monthly',
                'duration' => 2,
                'identifier' => 'premium',
                'price' => 100.00,
                'total_price' => 100.00,
                'tax' => 0.00,
                'currency' => 'inr',
                'permission_ids' => json_encode(explode(',', 'view_branch,add_branch,edit_branch,delete_branch,branch_gallery,view_dashboard,add_dashboard,edit_dashboard,delete_dashboard,view_booking,add_booking,edit_booking,delete_booking,booking_booking_tableview,view_service,add_service,edit_service,delete_service,service_gallery,view_category,add_category,edit_category,delete_category,view_package,add_package,edit_package,delete_package,view_subcategory,add_subcategory,edit_subcategory,delete_subcategory,view_promotion,add_promotion,edit_promotion,delete_promotion,view_staff,add_staff,edit_staff,delete_staff,staff_password,view_customer,add_customer,edit_customer,delete_customer,customer_password,view_page,add_page,edit_page,delete_page,view_tax,add_tax,edit_tax,delete_tax,view_review,add_review,edit_review,delete_review,view_setting,setting_general,setting_misc,setting_quick_booking,setting_customization,setting_mail,setting_currency,setting_commission,setting_holiday,setting_bussiness_hours,setting_language,view_staff_earning,add_staff_earning,edit_staff_earning,delete_staff_earning,add_report,edit_report,delete_report,view_staff_service,add_staff_service,edit_staff_service,delete_staff_service,view_location,add_location,edit_location,delete_location,view_report,view_staff_payout,add_staff_payout,edit_staff_payout,delete_staff_payout,view_notification_list,add_notification_list,edit_notification_list,delete_notification_list,view_notification_template,add_notification_template,edit_notification_template,delete_notification_template,view_app_banner,add_app_banner,edit_app_banner,delete_app_banner,view_access_control,add_access_control,edit_access_control,delete_access_control,view_product,add_product,edit_product,delete_product,view_product_variations,add_product_variations,edit_product_variations,delete_product_variations,view_product_orders,add_product_orders,edit_product_orders,delete_product_orders,view_logistics,add_logistics,edit_logistics,delete_logistics,view_shipping_zone,add_shipping_zone,edit_shipping_zone,delete_shipping_zone,view_faq,add_faq,edit_faq,delete_faq,view_blog,add_blog,edit_blog,delete_blog,view_user_inquiry,add_user_inquiry,edit_user_inquiry,delete_user_inquiry')),
                'description' => 'Suitable for growing businesses with multiple branches, more staff, and expanded service offerings.',
                'status' => 1,
                'max_appointment' => 10,
                'max_branch' => 2,
                'max_service' => 10,
                'max_staff' => 5,
                'max_customer' => 5,
                'created_by' => 1,
                'updated_by' => 1,
                'is_free_plan' => 0,
            ],
            [
                'name' => 'Elite',
                'type' => 'Yearly',
                'duration' => 1,
                'identifier' => 'elite',
                'price' => 200.00,
                'total_price' => 200.00,
                'tax' => 0.00,
                'currency' => 'inr',
                'permission_ids' => json_encode(explode(',', 'view_branch,add_branch,edit_branch,delete_branch,branch_gallery,view_dashboard,add_dashboard,edit_dashboard,delete_dashboard,view_booking,add_booking,edit_booking,delete_booking,booking_booking_tableview,view_service,add_service,edit_service,delete_service,service_gallery,view_category,add_category,edit_category,delete_category,view_package,add_package,edit_package,delete_package,view_subcategory,add_subcategory,edit_subcategory,delete_subcategory,view_promotion,add_promotion,edit_promotion,delete_promotion,view_staff,add_staff,edit_staff,delete_staff,staff_password,view_customer,add_customer,edit_customer,delete_customer,customer_password,view_page,add_page,edit_page,delete_page,view_tax,add_tax,edit_tax,delete_tax,view_review,add_review,edit_review,delete_review,view_setting,setting_general,setting_misc,setting_quick_booking,setting_customization,setting_mail,setting_currency,setting_commission,setting_holiday,setting_bussiness_hours,setting_language,view_staff_earning,add_staff_earning,edit_staff_earning,delete_staff_earning,add_report,edit_report,delete_report,view_staff_service,add_staff_service,edit_staff_service,delete_staff_service,view_location,add_location,edit_location,delete_location,view_report,view_staff_payout,add_staff_payout,edit_staff_payout,delete_staff_payout,view_notification_list,add_notification_list,edit_notification_list,delete_notification_list,view_notification_template,add_notification_template,edit_notification_template,delete_notification_template,view_app_banner,add_app_banner,edit_app_banner,delete_app_banner,view_access_control,add_access_control,edit_access_control,delete_access_control,view_product,add_product,edit_product,delete_product,view_product_variations,add_product_variations,edit_product_variations,delete_product_variations,view_product_orders,add_product_orders,edit_product_orders,delete_product_orders,view_logistics,add_logistics,edit_logistics,delete_logistics,view_shipping_zone,add_shipping_zone,edit_shipping_zone,delete_shipping_zone,view_faq,add_faq,edit_faq,delete_faq,view_blog,add_blog,edit_blog,delete_blog,view_user_inquiry,add_user_inquiry,edit_user_inquiry,delete_user_inquiry')),
                'description' => 'Designed for large-scale businesses with extensive service offerings, multiple branches, and full feature access.',
                'status' => 1,
                'max_appointment' => 100,
                'max_branch' => 10,
                'max_service' => 50,
                'max_staff' => 20,
                'max_customer' => 50,
                'created_by' => 1,
                'updated_by' => 1,
                'is_free_plan' => 0,
            ],
            [
                'name' => 'Super Elite',
                'type' => 'Yearly',
                'identifier' => 'super_elite',
                'duration' => 1,
                'price' => 500.00,
                'total_price' => 500.00,
                'tax' => 0.00,
                'currency' => 'inr',
                'permission_ids' => json_encode(explode(',', 'view_branch,add_branch,edit_branch,delete_branch,branch_gallery,view_dashboard,add_dashboard,edit_dashboard,delete_dashboard,view_booking,add_booking,edit_booking,delete_booking,booking_booking_tableview,view_service,add_service,edit_service,delete_service,service_gallery,view_category,add_category,edit_category,delete_category,view_package,add_package,edit_package,delete_package,view_subcategory,add_subcategory,edit_subcategory,delete_subcategory,view_promotion,add_promotion,edit_promotion,delete_promotion,view_staff,add_staff,edit_staff,delete_staff,staff_password,view_customer,add_customer,edit_customer,delete_customer,customer_password,view_page,add_page,edit_page,delete_page,view_tax,add_tax,edit_tax,delete_tax,view_review,add_review,edit_review,delete_review,view_setting,setting_general,setting_misc,setting_quick_booking,setting_customization,setting_mail,setting_currency,setting_commission,setting_holiday,setting_bussiness_hours,setting_language,view_staff_earning,add_staff_earning,edit_staff_earning,delete_staff_earning,add_report,edit_report,delete_report,view_staff_service,add_staff_service,edit_staff_service,delete_staff_service,view_location,add_location,edit_location,delete_location,view_report,view_staff_payout,add_staff_payout,edit_staff_payout,delete_staff_payout,view_notification_list,add_notification_list,edit_notification_list,delete_notification_list,view_notification_template,add_notification_template,edit_notification_template,delete_notification_template,view_app_banner,add_app_banner,edit_app_banner,delete_app_banner,view_access_control,add_access_control,edit_access_control,delete_access_control,view_product,add_product,edit_product,delete_product,view_product_variations,add_product_variations,edit_product_variations,delete_product_variations,view_product_orders,add_product_orders,edit_product_orders,delete_product_orders,view_logistics,add_logistics,edit_logistics,delete_logistics,view_shipping_zone,add_shipping_zone,edit_shipping_zone,delete_shipping_zone,view_faq,add_faq,edit_faq,delete_faq,view_blog,add_blog,edit_blog,delete_blog,view_user_inquiry,add_user_inquiry,edit_user_inquiry,delete_user_inquiry')),
                'description' => 'The Super Elite plan is designed for enterprise-level businesses that require unlimited access to all features, multiple branches, a large customer base, and advanced tools for scaling operations seamlessly.',
                'status' => 1,
                'max_appointment' => 200,
                'max_branch' => 20,
                'max_service' => 70,
                'max_staff' => 50,
                'max_customer' => 150,
                'created_by' => 1,
                'updated_by' => 1,
                'is_free_plan' => 0,
            ],
            [
                'name' => 'Standard',
                'type' => 'Monthly',
                'identifier' => 'standard',
                'duration' => 3,
                'price' => 150.00,
                'total_price' => 150.00,
                'tax' => 0.00,
                'currency' => 'inr',
                'permission_ids' => json_encode(explode(',', 'view_branch,add_branch,edit_branch,delete_branch,branch_gallery,view_dashboard,add_dashboard,edit_dashboard,delete_dashboard,view_booking,add_booking,edit_booking,delete_booking,booking_booking_tableview,view_service,add_service,edit_service,delete_service,service_gallery,view_category,add_category,edit_category,delete_category,view_package,add_package,edit_package,delete_package,view_subcategory,add_subcategory,edit_subcategory,delete_subcategory,view_promotion,add_promotion,edit_promotion,delete_promotion,view_staff,add_staff,edit_staff,delete_staff,staff_password,view_customer,add_customer,edit_customer,delete_customer,customer_password,view_page,add_page,edit_page,delete_page,view_tax,add_tax,edit_tax,delete_tax,view_review,add_review,edit_review,delete_review,view_setting,setting_general,setting_misc,setting_quick_booking,setting_customization,setting_mail,setting_currency,setting_commission,setting_holiday,setting_bussiness_hours,setting_language,view_staff_earning,add_staff_earning,edit_staff_earning,delete_staff_earning,add_report,edit_report,delete_report,view_staff_service,add_staff_service,edit_staff_service,delete_staff_service,view_location,add_location,edit_location,delete_location,view_report,view_staff_payout,add_staff_payout,edit_staff_payout,delete_staff_payout,view_notification_list,add_notification_list,edit_notification_list,delete_notification_list,view_notification_template,add_notification_template,edit_notification_template,delete_notification_template,view_app_banner,add_app_banner,edit_app_banner,delete_app_banner,view_access_control,add_access_control,edit_access_control,delete_access_control,view_product,add_product,edit_product,delete_product,view_product_variations,add_product_variations,edit_product_variations,delete_product_variations,view_product_orders,add_product_orders,edit_product_orders,delete_product_orders,view_logistics,add_logistics,edit_logistics,delete_logistics,view_shipping_zone,add_shipping_zone,edit_shipping_zone,delete_shipping_zone,view_faq,add_faq,edit_faq,delete_faq,view_blog,add_blog,edit_blog,delete_blog,view_user_inquiry,add_user_inquiry,edit_user_inquiry,delete_user_inquiry')),
                'description' => 'The Standard plan is perfect for small to mid-sized businesses looking for essential features to manage their operations smoothly.',
                'status' => 1,
                'max_appointment' => 20,
                'max_branch' => 5,
                'max_service' => 25,
                'max_staff' => 10,
                'max_customer' => 15,
                'created_by' => 1,
                'updated_by' => 1,
                'is_free_plan' => 0,
            ],
            [
                'name' => 'Standard Elite',
                'type' => 'Yearly',
                'identifier' => 'standard_elite',
                'duration' => 1,
                'price' => 1000.00,
                'total_price' => 1000.00,
                'tax' => 0.00,
                'currency' => 'inr',
                'permission_ids' => json_encode(explode(',', 'view_branch,add_branch,edit_branch,delete_branch,branch_gallery,view_dashboard,add_dashboard,edit_dashboard,delete_dashboard,view_booking,add_booking,edit_booking,delete_booking,booking_booking_tableview,view_service,add_service,edit_service,delete_service,service_gallery,view_category,add_category,edit_category,delete_category,view_package,add_package,edit_package,delete_package,view_subcategory,add_subcategory,edit_subcategory,delete_subcategory,view_promotion,add_promotion,edit_promotion,delete_promotion,view_staff,add_staff,edit_staff,delete_staff,staff_password,view_customer,add_customer,edit_customer,delete_customer,customer_password,view_page,add_page,edit_page,delete_page,view_tax,add_tax,edit_tax,delete_tax,view_review,add_review,edit_review,delete_review,view_setting,setting_general,setting_misc,setting_quick_booking,setting_customization,setting_mail,setting_currency,setting_commission,setting_holiday,setting_bussiness_hours,setting_language,view_staff_earning,add_staff_earning,edit_staff_earning,delete_staff_earning,add_report,edit_report,delete_report,view_staff_service,add_staff_service,edit_staff_service,delete_staff_service,view_location,add_location,edit_location,delete_location,view_report,view_staff_payout,add_staff_payout,edit_staff_payout,delete_staff_payout,view_notification_list,add_notification_list,edit_notification_list,delete_notification_list,view_notification_template,add_notification_template,edit_notification_template,delete_notification_template,view_app_banner,add_app_banner,edit_app_banner,delete_app_banner,view_access_control,add_access_control,edit_access_control,delete_access_control,view_product,add_product,edit_product,delete_product,view_product_variations,add_product_variations,edit_product_variations,delete_product_variations,view_product_orders,add_product_orders,edit_product_orders,delete_product_orders,view_logistics,add_logistics,edit_logistics,delete_logistics,view_shipping_zone,add_shipping_zone,edit_shipping_zone,delete_shipping_zone,view_faq,add_faq,edit_faq,delete_faq,view_blog,add_blog,edit_blog,delete_blog,view_user_inquiry,add_user_inquiry,edit_user_inquiry,delete_user_inquiry')),
                'description' => 'The Standard plan is perfect for small to mid-sized businesses looking for essential features to manage their operations smoothly.',
                'status' => 1,
                'max_appointment' => 200,
                'max_branch' => 20,
                'max_service' => 70,
                'max_staff' => 50,
                'max_customer' => 150,
                'created_by' => 1,
                'updated_by' => 1,
                'is_free_plan' => 0,
            ],
        ];

        foreach ($plans as $planData) {
            Plan::updateOrCreate(
                ['name' => $planData['name']], // Unique key to avoid duplicates
                $planData
            );
        }
    }
}
