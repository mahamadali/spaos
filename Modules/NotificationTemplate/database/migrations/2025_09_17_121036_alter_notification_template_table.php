<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\NotificationTemplate\Models\NotificationTemplate;
use Modules\NotificationTemplate\Models\NotificationTemplateContentMapping;
use App\Models\User;
use Modules\Constant\Models\Constant;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
         $types = [
            ['type' => 'notification_type', 'value' => 'order_placed', 'name' => 'Order Placed'],
            ['type' => 'notification_type', 'value' => 'order_proccessing', 'name' => 'Order Processing'],
            ['type' => 'notification_type', 'value' => 'order_delivered', 'name' => 'Order Delivered'],
            ['type' => 'notification_type', 'value' => 'order_cancelled', 'name' => 'Order Cancelled'],
            ['type' => 'notification_param_button', 'value' => 'order_id', 'name' => 'Order ID'],
            ['type' => 'notification_param_button', 'value' => 'order_date', 'name' => 'Order Date'],
            ['type' => 'notification_param_button', 'value' => 'order_status', 'name' => 'Order Status'],
            ['type' => 'notification_param_button', 'value' => 'delivery_address', 'name' => 'Delivery Address'],
            ['type' => 'notification_param_button', 'value' => 'check_out_time', 'name' => 'Check-out Time'],
            ['type' => 'notification_param_button', 'value' => 'total_amount', 'name' => 'Total Amount'],
            ['type' => 'notification_to', 'value' => 'user', 'name' => 'User'],
            ['type' => 'notification_to', 'value' => 'admin', 'name' => 'Admin'],
        ];

        foreach ($types as $value) {
            Constant::updateOrCreate(
                ['type' => $value['type'], 'value' => $value['value']],
                $value
            );
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $adminUsers = User::whereIn('user_type', ['super admin', 'admin'])->get();

        foreach ($adminUsers as $adminUser) {
            $adminId = $adminUser->id;

            $notifications = [
                'order_placed' => [
                    'label' => 'Order Placed',
                    'admin_message' => 'Thank you for choosing Us for your recent order...',
                    'user_message' => 'Thank you for placing your order with us! We are processing it...',
                    'admin_subject' => 'Order Placed!',
                    'user_subject' => 'Your Order has been Placed!',
                ],
                'order_proccessing' => [
                    'label' => 'Order Processing',
                    'admin_message' => 'An order is currently being processed...',
                    'user_message' => "We're excited to let you know that your order is now being prepared...",
                    'admin_subject' => 'Order Processing!',
                    'user_subject' => 'Order is Being Prepared!',
                ],
                'order_delivered' => [
                    'label' => 'Order Delivered',
                    'admin_message' => 'An order has been delivered to the customer.',
                    'user_message' => "We're delighted to inform you that your order has been delivered to your doorstep.",
                    'admin_subject' => 'Order Delivered!',
                    'user_subject' => 'Your Order is Delivered!',
                ],
                'order_cancelled' => [
                    'label' => 'Order Cancelled',
                    'admin_message' => 'An order has been cancelled by the user.',
                    'user_message' => 'We regret to inform you that your recent order has been cancelled.',
                    'admin_subject' => 'Order Cancelled!',
                    'user_subject' => 'Order Cancelled!',
                ],
            ];

            foreach ($notifications as $type => $data) {
                $template = NotificationTemplate::updateOrCreate(
                    ['type' => $type, 'created_by' => $adminId],
                    [
                        'name' => $type,
                        'label' => $data['label'],
                        'status' => 1,
                        'to' => '["user","admin"]',
                        'channels' => ['IS_MAIL' => '1', 'PUSH_NOTIFICATION' => '1', 'IS_CUSTOM_WEBHOOK' => '0'],
                        'created_by' => $adminId,
                    ]
                );

                // Admin template
                $template->defaultNotificationTemplateMap()->updateOrCreate(
                    ['language' => 'en', 'user_type' => 'admin', 'created_by' => $adminId],
                    [
                        'notification_link' => '',
                        'notification_message' => $data['admin_message'],
                        'status' => 1,
                        'template_detail' => '<p>Dear [[ admin_name ]],</p><p>' . $data['admin_message'] . '</p><p>[[ company_name ]]</p>',
                        'subject' => $data['admin_subject'],
                        'notification_subject' => $data['admin_subject'],
                        'notification_template_detail' => '<p>' . $data['admin_message'] . '</p>',
                        'created_by' => $adminId,
                    ]
                );

                // User template
                $template->defaultNotificationTemplateMap()->updateOrCreate(
                    ['language' => 'en', 'user_type' => 'user', 'created_by' => $adminId],
                    [
                        'notification_link' => '',
                        'notification_message' => $data['user_message'],
                        'status' => 1,
                        'template_detail' => '<p>Dear [[ user_name ]],</p><p>' . $data['user_message'] . '</p><p>[[ company_name ]]</p>',
                        'subject' => $data['user_subject'],
                        'notification_subject' => $data['user_subject'],
                        'notification_template_detail' => '<p>' . $data['user_message'] . '</p>',
                        'created_by' => $adminId,
                    ]
                );
            }
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
