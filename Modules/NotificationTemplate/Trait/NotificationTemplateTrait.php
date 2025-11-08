<?php

namespace Modules\NotificationTemplate\Trait;
use Modules\NotificationTemplate\Models\NotificationTemplate;

trait NotificationTemplateTrait
{
    public function addNotificationTemplate($adminId)
    {
        $template = NotificationTemplate::create([
            'type' => 'new_booking',
            'name' => 'new_booking',
            'label' => 'Booking confirmation',
            'status' => 1,
            'to' => '["admin", "user"]', // Changed to include 'vendor'
            'channels' => ['IS_MAIL' => '1', 'PUSH_NOTIFICATION' => '1', 'IS_CUSTOM_WEBHOOK' => '0'],
            'created_by' => $adminId,
        ]);

        // Notification template for admin
        $template->defaultNotificationTemplateMap()->create([
            'language' => 'en',
            'notification_link' => '',
            'notification_message' => 'Thank you for choosing our services! Your booking has been successfully confirmed. We look forward to serving you and providing an exceptional experience. Stay tuned for further updates.',
            'status' => 1,
            'user_type' => 'admin',
            'template_detail' => '
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;">
            <span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">Subject: Appointment Confirm - Thank You!</span>
            </p>
            <p><strong id="docs-internal-guid-7d6bdcce-7fff-5035-731b-386f9021a5db" style="font-weight: normal;">&nbsp;</strong></p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;">
            <span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">Dear [[ user_name ]],</span>
            </p>
            <p><strong style="font-weight: normal;">&nbsp;</strong></p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;">
            <span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">We are delighted to inform you that your appointment has been successfully confirmed! Thank you for choosing our services. We are excited to have you as our valued customer and are committed to providing you with a wonderful experience.</span>
            </p>
            <p><strong style="font-weight: normal;">&nbsp;</strong></p>
            <h4>Appointment Details</h4>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;">
            <span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">Appointment ID: [[ id ]]</span>
            </p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;">
            <span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">Appointment Date: [[ booking_date ]]</span>
            </p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;">
            <span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">Service/Event: [[ service_name ]]</span>
            </p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;">
            <span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">Date: [[ booking_date ]]</span>
            </p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;">
            <span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">Time: [[ booking_time ]]</span>
            </p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;">
            <span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">Location: [[ venue_address ]]</span>
            </p>
            <p><strong style="font-weight: normal;">&nbsp;</strong></p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;">
            <span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">We want to assure you that we have received your appointment details and everything is in order. Our team is eagerly preparing to make this a memorable experience for you. If you have any specific requirements or questions regarding your appointment, please feel free to reach out to us.</span>
            </p>
            <p><strong style="font-weight: normal;">&nbsp;</strong></p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;">
            <span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">We recommend marking your calendar and setting a reminder for the date and time of the event to ensure you don\'t miss your appointment. Should there be any updates or changes to your appointment, we will promptly notify you.</span>
            </p>
            <p><strong style="font-weight: normal;">&nbsp;</strong></p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;">
            <span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">Once again, thank you for choosing our services. We look forward to providing you with exceptional service and creating lasting memories. If you have any further queries, please do not hesitate to contact our friendly customer support team.</span>
            </p>
            <p><strong style="font-weight: normal;">&nbsp;</strong></p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;">
            <span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">Best regards,</span>
            </p>
            <p><strong style="font-weight: normal;">&nbsp;</strong></p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;">
            <span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">[[ logged_in_user_fullname ]],</span>
            </p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;">
            <span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">[[ logged_in_user_role ]],</span>
            </p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;">
            <span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">[[ company_name ]],</span>
            </p>
            <p>&nbsp;</p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;">
            <span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">[[ company_contact_info ]]</span>
            </p>
            <p><span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">&nbsp;</span></p>
            ',
            'subject' => 'Booking Confirmation Received!',
            'notification_subject' => 'New Booking Alert.',
                'notification_template_detail' => '<p>New booking: [[ user_name ]] has booked [[ service_name ]].</p>',
        ]);

        // Notification template for vendor
        $template->defaultNotificationTemplateMap()->create([
            'language' => 'en',
            'notification_link' => '',
            'notification_message' => 'Thank you for choosing our services! Your booking has been successfully confirmed. We look forward to serving you and providing an exceptional experience. Stay tuned for further updates.',
            'status' => 1,
            'user_type' => 'user',
           'template_detail' => '
<p style="font-family: Arial; font-size: 11pt; color: #000;">Subject: Appointment Confirm - Thank You!</p>

<p>Dear [[ user_name ]],</p>

<p>We are delighted to inform you that your appointment has been <strong>successfully confirmed</strong>! Thank you for choosing our services. We are excited to have you as our valued customer and are committed to providing you with a wonderful experience.</p>

<h4>Appointment Details</h4>
<ul style="font-family: Arial; font-size: 11pt; color: #000;">
  <li><strong>Appointment ID:</strong> [[ id ]]</li>
  <li><strong>Appointment Date:</strong> [[ booking_date ]]</li>
  <li><strong>Service/Event:</strong> [[ service_name ]]</li>
  <li><strong>Date:</strong> [[ booking_date ]]</li>
  <li><strong>Time:</strong> [[ booking_time ]]</li>
  <li><strong>Location:</strong> [[ venue_address ]]</li>
</ul>

<p>We have received your appointment details, and everything is in order. Our team is preparing to make this a great experience for you. If you have any specific requirements or questions, feel free to contact us.</p>

<p>We recommend marking your calendar and setting a reminder so you don\'t miss your appointment. If there are any changes, we will inform you promptly.</p>

<p>Thank you once again for trusting us. We look forward to serving you!</p>

<p>Best regards,<br>
[[ logged_in_user_fullname ]]<br>
[[ company_name ]]</p>
',
          
            'subject' => 'Booking Confirmation Received!',
            'notification_subject' => 'Booking Confirmed',
                'notification_template_detail' => 'We are delighted to confirm your appointment. Thank you for choosing our services. See details below.',
        ]);


        $template = NotificationTemplate::create([
            'type' => 'check_in_booking',
            'name' => 'check_in_booking',
            'label' => 'Check-In On Booking',
            'status' => 1,
            'to' => '["user"]', // Changed to 'vendor'
            'channels' => ['IS_MAIL' => '1', 'PUSH_NOTIFICATION' => '1', 'IS_CUSTOM_WEBHOOK' => '0'],
            'created_by' => $adminId,
        ]);
        $template->defaultNotificationTemplateMap()->create([
            'language' => 'en',
            'notification_link' => '',
            'notification_message' => 'Welcome to your booked accommodation. We hope you have a pleasant stay!',
            'status' => 1,
            'user_type' => 'user', // Changed to 'vendor'
            'template_detail' => '<p><span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">Subject: Appointment C<span style="font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 14px; white-space-collapse: collapse;">heck in</span> - Thank You!</span></p>
            <p><span id="docs-internal-guid-7d6bdcce-7fff-5035-731b-386f9021a5db">&nbsp;</span></p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;"><span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">Dear [[ user_name ]],</span></p>
            <p>&nbsp;</p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;">Welcome to your booked accommodation. We hope you have a pleasant stay!</p>
            <p>&nbsp;</p>
            <h4>Appointment Details</h4>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;"><span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">Appointment ID: [[ id ]]</span></p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;"><span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">Appointment Date: [[ booking_date ]]</span></p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;"><span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">Service/Event: [[ service_name ]]</span></p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;"><span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">Date: [[ booking_date ]]</span></p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;"><span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">Time: [[ booking_time ]]</span></p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;"><span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">Location: [[ venue_address ]]</span></p>
            <p>&nbsp;</p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;"><span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">We want to assure you that we have received your appointment details and everything is in order. Our team is eagerly preparing to make this a memorable experience for you. If you have any specific requirements or questions regarding your appointment, please feel free to reach out to us.</span></p>
            <p>&nbsp;</p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;"><span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">We recommend marking your calendar and setting a reminder for the date and time of the event to ensure you don\'t miss your appointment. Should there be any updates or changes to your appointment, we will promptly notify you.</span></p>
            <p>&nbsp;</p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;"><span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">Once again, thank you for choosing our services. We look forward to providing you with exceptional service and creating lasting memories. If you have any further queries, please do not hesitate to contact our friendly customer support team.</span></p>
            <p>&nbsp;</p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;"><span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">Best regards,</span></p>
            <p>&nbsp;</p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;"><span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">[[ logged_in_user_fullname ]],</span></p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;"><span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">[[ logged_in_user_role ]],</span></p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;"><span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">[[ company_name ]],</span></p>
            <p>&nbsp;</p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;"><span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">[[ company_contact_info ]]</span></p>
            <p>&nbsp;</p>
            <p>&nbsp;</p>
            <p><span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">&nbsp;</span></p>',
            'subject' => 'Check-in Successful!',
            'notification_subject' => 'Check-in Successful!',
            'notification_template_detail' => '<p>Welcome to your booked accommodation. We hope you have a pleasant stay!</p>',
            'created_by' => $adminId,
        ]);

        $template = NotificationTemplate::create([
            'type' => 'checkout_booking',
            'name' => 'checkout_booking',
            'label' => 'Checkout On Booking',
            'status' => 1,
            'to' => '["user"]', // Changed to 'vendor'
            'channels' => ['IS_MAIL' => '1', 'PUSH_NOTIFICATION' => '1', 'IS_CUSTOM_WEBHOOK' => '0'],
            'created_by' => $adminId,
        ]);
        $template->defaultNotificationTemplateMap()->create([
            'language' => 'en',
            'notification_link' => '',
            'notification_message' => 'Thank you for choosing our services. Please remember to check out by [[ checkout_time ]]. We hope you had a wonderful experience!',
            'status' => 1,
            'user_type' => 'user', // Changed to 'vendor'
            'template_detail' => '<p><span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">Subject: Appointment C<span style="font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 14px; white-space-collapse: collapse;">heck out</span> - Thank You!</span></p>
            <p><span id="docs-internal-guid-7d6bdcce-7fff-5035-731b-386f9021a5db">&nbsp;</span></p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;"><span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">Dear [[ user_name ]],</span></p>
            <p><span>&nbsp;</span></p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;">Thank you for choosing our services. Please remember to check out by [[ checkout_time ]]. We hope you had a wonderful experience!</p>
            <p><span>&nbsp;</span></p>
            <h4>Appointment Details</h4>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;"><span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">Appointment ID: [[ id ]]</span></p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;"><span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">Appointment Date: [[ booking_date ]]</span></p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;"><span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">Service/Event: [[ service_name ]]</span></p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;"><span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">Date: [[ booking_date ]]</span></p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;"><span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">Time: [[ booking_time ]]</span></p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;"><span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">Location: [[ venue_address ]]</span></p>
            <p><span>&nbsp;</span></p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;"><span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">We want to assure you that we have received your appointment details and everything is in order. Our team is eagerly preparing to make this a memorable experience for you. If you have any specific requirements or questions regarding your appointment, please feel free to reach out to us.</span></p>
            <p><span>&nbsp;</span></p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;"><span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">We recommend marking your calendar and setting a reminder for the date and time of the event to ensure you don\'t miss your appointment. Should there be any updates or changes to your appointment, we will promptly notify you.</span></p>
            <p><span>&nbsp;</span></p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;"><span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">Once again, thank you for choosing our services. We look forward to providing you with exceptional service and creating lasting memories. If you have any further queries, please do not hesitate to contact our friendly customer support team.</span></p>
            <p><span>&nbsp;</span></p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;"><span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">Best regards,</span></p>
            <p><span>&nbsp;</span></p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;"><span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">[[ logged_in_user_fullname ]],</span></p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;"><span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">[[ logged_in_user_role ]],</span></p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;"><span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">[[ company_name ]],</span></p>
            <p>&nbsp;</p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;"><span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">[[ company_contact_info ]]</span></p>
            <p>&nbsp;</p>
            <p><span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">&nbsp;</span></p>',
            'subject' => 'Check-out Reminder',
            'notification_subject' => 'Check-out Reminder',
            'notification_template_detail' => '<p>Thank you for choosing our services. Please remember to check out by  [[ checkout_time ]]. We hope you had a wonderful experience!</p>',
            'created_by' => $adminId,
        ]);

        $template = NotificationTemplate::create([
            'type' => 'complete_booking',
            'name' => 'complete_booking',
            'label' => 'Complete On Booking',
            'status' => 1,
            'to' => '["user"]', // Changed to 'vendor'
            'channels' => [ 'PUSH_NOTIFICATION' => '1', 'IS_MAIL' => '1','IS_CUSTOM_WEBHOOK' => '0'],
            'created_by' => $adminId,
        ]);
        $template->defaultNotificationTemplateMap()->create([
            'language' => 'en',
            'notification_link' => '',
            'notification_message' => 'Congratulations! Your booking has been successfully completed. We appreciate your business and look forward to serving you again.',
            'status' => 1,
            'user_type' => 'user', // Changed to 'vendor'
            'language' => 'en',
            'template_detail' => '<p>Subject: Appointment Completion and Invoice</p>
            <p>&nbsp;</p>
            <p>Dear [[ user_name ]],</p>
            <p>&nbsp;</p>
            <p>We are writing to inform you that your recent appointment with us has been successfully completed. We sincerely appreciate your trust in our services and the opportunity to serve you.</p>
            <p>&nbsp;</p>
            <p>[[ company_name ]]</p>',
            'subject' => 'Appointment complete email with invoice',
            'notification_subject' => 'Appointment complete email with invoice',
            'notification_template_detail' => '<p>We are writing to inform you that your recent appointment with us has been successfully completed.</p>',
            'created_by' => $adminId,
        ]);

        $template = NotificationTemplate::create([
            'type' => 'cancel_booking',
            'name' => 'cancel_booking',
            'label' => 'Cancel On Booking',
            'status' => 1,
            'to' => '["user"]', // Changed to 'vendor'
            'channels' => ['IS_MAIL' => '1', 'PUSH_NOTIFICATION' => '1', 'IS_CUSTOM_WEBHOOK' => '0'],
            'created_by' => $adminId,
        ]);
        $template->defaultNotificationTemplateMap()->create([
            'language' => 'en',
            'notification_link' => '',
            'notification_message' => 'We regret to inform you that your booking has been cancelled. If you have any questions or need further assistance, please contact our support team.',
            'status' => 1,
            'user_type' => 'user', // Changed to 'vendor'
            'template_detail' => '<p>Dear [[ user_name ]],</p>
            <p>&nbsp;</p>
            <p>We regret to inform you that your booking has been cancelled. If you have any questions or need further assistance, please contact our support team.</p>
            <p>&nbsp;</p>
            <p>Thank you for your understanding.</p>
            <p>&nbsp;</p>
            <p>Best regards,</p>
            <p>&nbsp;</p>
            <p>&nbsp;</p>
            <p>[[ company_name ]]</p>',
            'subject' => 'Booking Cancellation',
            'notification_subject' => 'Important: Booking Cancellation Notice',
            'notification_template_detail' => '<p><span style="font-family: Arial; font-size: 14.6667px; white-space-collapse: preserve;">We regret to inform you that your booking has been cancelled. If you have any questions or need further assistance, please contact our support team.</span></p>',
            'created_by' => $adminId,
        ]);

        $template = NotificationTemplate::create([
            'type' => 'quick_booking',
            'name' => 'quick_booking',
            'label' => 'Quick Booking',
            'status' => 1,
            'to' => '["user","admin"]', // Changed to 'vendor'
            'channels' => ['IS_MAIL' => '1', 'PUSH_NOTIFICATION' => '1', 'IS_CUSTOM_WEBHOOK' => '0'],
            'created_by' => $adminId,
        ]);
        $template->defaultNotificationTemplateMap()->create([
            'language' => 'en',
            'notification_link' => '',
            'notification_message' => '',
            'status' => 1,
            'user_type' => 'user', // Changed to 'vendor'
            'subject' => 'Quick Booking',
            'notification_template_detail' => '
                <p>We are pleased to inform you that your appointment has been successfully booked. We value your time and are committed to providing you with excellent service.</p>
            ',
            'notification_subject' => 'Your Appointment Confirmation',
            'template_detail' => '
                <p>Dear [[ user_name ]],</p>
                <p>&nbsp;</p>
                <p>Your appointment has been confirmed. Below are the details:</p>
                <p>&nbsp;</p>
                <p>Appointment Date: [[ booking_date ]]</p>
                <p>Appointment Time: [[ booking_time ]]</p>
                <p>Appointment Duration: [[ booking_duration ]]</p>
                <p>Location: [[ venue_address ]]</p>
                <p>&nbsp;</p>
                <p>Please arrive a few minutes early to ensure a smooth experience. If you need to reschedule or cancel, notify us at least [[ link ]] in advance.</p>
                <p>&nbsp;</p>
                <p>Thank you for choosing our services.</p>
                <p>&nbsp;</p>
                <p>Best regards,</p>
                <p>&nbsp;</p>
                <p>[[ company_name ]]</p>
            ',
            'created_by' => $adminId,
        ]);

        $template->defaultNotificationTemplateMap()->create([
            'language' => 'en',
            'notification_link' => '',
            'notification_message' => 'Thank you for choosing our services! Your booking has been successfully confirmed. We look forward to serving you and providing an exceptional experience. Stay tuned for further updates.',
            'status' => 1,
            'user_type' => 'admin',
            'template_detail' => '
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;">
            <span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">Subject: Appointment Confirm - Thank You!</span>
            </p>
            <p><strong id="docs-internal-guid-7d6bdcce-7fff-5035-731b-386f9021a5db" style="font-weight: normal;">&nbsp;</strong></p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;">
            <span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">Dear [[ user_name ]],</span>
            </p>
            <p><strong style="font-weight: normal;">&nbsp;</strong></p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;">
            <span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">We are delighted to inform you that your appointment has been successfully confirmed! Thank you for choosing our services. We are excited to have you as our valued customer and are committed to providing you with a wonderful experience.</span>
            </p>
            <p><strong style="font-weight: normal;">&nbsp;</strong></p>
            <h4>Appointment Details</h4>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;">
            <span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">Appointment ID: [[ id ]]</span>
            </p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;">
            <span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">Appointment Date: [[ booking_date ]]</span>
            </p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;">
            <span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">Service/Event: [[ service_name ]]</span>
            </p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;">
            <span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">Date: [[ booking_date ]]</span>
            </p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;">
            <span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">Time: [[ booking_time ]]</span>
            </p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;">
            <span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">Location: [[ venue_address ]]</span>
            </p>
            <p><strong style="font-weight: normal;">&nbsp;</strong></p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;">
            <span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">We want to assure you that we have received your appointment details and everything is in order. Our team is eagerly preparing to make this a memorable experience for you. If you have any specific requirements or questions regarding your appointment, please feel free to reach out to us.</span>
            </p>
            <p><strong style="font-weight: normal;">&nbsp;</strong></p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;">
            <span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">We recommend marking your calendar and setting a reminder for the date and time of the event to ensure you don\'t miss your appointment. Should there be any updates or changes to your appointment, we will promptly notify you.</span>
            </p>
            <p><strong style="font-weight: normal;">&nbsp;</strong></p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;">
            <span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">Once again, thank you for choosing our services. We look forward to providing you with exceptional service and creating lasting memories. If you have any further queries, please do not hesitate to contact our friendly customer support team.</span>
            </p>
            <p><strong style="font-weight: normal;">&nbsp;</strong></p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;">
            <span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">Best regards,</span>
            </p>
            <p><strong style="font-weight: normal;">&nbsp;</strong></p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;">
            <span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">[[ logged_in_user_fullname ]],</span>
            </p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;">
            <span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">[[ logged_in_user_role ]],</span>
            </p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;">
            <span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">[[ company_name ]],</span>
            </p>
            <p>&nbsp;</p>
            <p dir="ltr" style="line-height: 1.38; margin-top: 0pt; margin-bottom: 0pt;">
            <span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">[[ company_contact_info ]]</span>
            </p>
            <p><span style="font-size: 11pt; font-family: Arial; color: #000000; background-color: transparent; font-weight: 400; font-style: normal; font-variant: normal; text-decoration: none; vertical-align: baseline; white-space: pre-wrap;">&nbsp;</span></p>
            ',
            'subject' => 'Booking Confirmation Received!',
            'notification_subject' => 'New Booking Alert.',
            'notification_template_detail' => '<p>New booking: [[ user_name ]] has booked [[ service_name ]].</p>',
            'created_by' => $adminId,

        ]);

        $template = NotificationTemplate::create([
            'type' => 'change_password',
            'name' => 'change_password',
            'label' => 'Change Password',
            'status' => 1,
            'channels' => ['IS_MAIL' => '1', 'PUSH_NOTIFICATION' => '1', 'IS_CUSTOM_WEBHOOK' => '0'],
            'created_by' => $adminId,
        ]);
        $template->defaultNotificationTemplateMap()->create([
            'language' => 'en',
            'notification_link' => '',
            'notification_message' => '',
            'status' => 1,
            'subject' => 'Change Password',
            'template_detail' => '
            <p>Subject: Password Change Confirmation</p>
            <p>Dear [[ user_name ]],</p>
            <p>&nbsp;</p>
            <p>We wanted to inform you that a recent password change has been made for your account. If you did not initiate this change, please take immediate action to secure your account.</p>
            <p>&nbsp;</p>
            <p>To regain control and secure your account:</p>
            <p>&nbsp;</p>
            <p>Visit [[ link ]].</p>
            <p>Follow the instructions to verify your identity.</p>
            <p>Create a strong and unique password.</p>
            <p>Update passwords for any other accounts using similar credentials.</p>
            <p>If you have any concerns or need assistance, please contact our customer support team.</p>
            <p>&nbsp;</p>
            <p>Thank you for your attention to this matter.</p>
            <p>&nbsp;</p>
            <p>Best regards,</p>
            <p>[[ logged_in_user_fullname ]]<br />[[ logged_in_user_role ]]<br />[[ company_name ]]</p>
            <p>[[ company_contact_info ]]</p>
        ',
            'created_by' => $adminId,
        ]);

        $template = NotificationTemplate::create([
            'type' => 'forget_email_password',
            'name' => 'forget_email_password',
            'label' => 'Forget Email/Password',
            'status' => 1,
            'channels' => ['IS_MAIL' => '1', 'PUSH_NOTIFICATION' => '1', 'IS_CUSTOM_WEBHOOK' => '0'],
            'created_by' => $adminId,
        ]);
        $template->defaultNotificationTemplateMap()->create([
            'language' => 'en',
            'notification_link' => '',
            'notification_message' => '',
            'status' => 1,
            'subject' => 'Forget Email/Password',
            'template_detail' => '
            <p>Subject: Password Reset Instructions</p>
            <p>&nbsp;</p>
            <p>Dear [[ user_name ]],</p>
            <p>A password reset request has been initiated for your account. To reset your password:</p>
            <p>&nbsp;</p>
            <p>Visit [[ link ]].</p>
            <p>Enter your email address.</p>
            <p>Follow the instructions provided to complete the reset process.</p>
            <p>If you did not request this reset or need assistance, please contact our support team.</p>
            <p>&nbsp;</p>
            <p>Thank you.</p>
            <p>&nbsp;</p>
            <p>Best regards,</p>
            <p>[[ logged_in_user_fullname ]]<br />[[ logged_in_user_role ]]<br />[[ company_name ]]</p>
            <p>[[ company_contact_info ]]</p>
            <p>&nbsp;</p>
        ',
            'created_by' => $adminId,
        ]);

        // Creating the purchase plan notification template
        $template = NotificationTemplate::create([
            'type' => 'purchase_plan',
            'name' => 'purchase_plan',
            'label' => 'Purchase Plan',
            'created_by' => 1,
            'status' => 1,
            'to' => '["super admin"]',
            'channels' => ['IS_MAIL' => '1', 'PUSH_NOTIFICATION' => '1', 'IS_CUSTOM_WEBHOOK' => '0'],
            'created_by' => $adminId,
        ]);

        // Vendor notification template for purchase plan
        $template->defaultNotificationTemplateMap()->create([
            'language' => 'en',
            'notification_link' => '',
            'notification_message' => 'Thank you for purchasing a plan with us.',
            'status' => 1,
            'user_type' => 'vendor', // Changed to 'vendor'
            'template_detail' => '<p>Dear [[ user_name ]],</p>
            <p>Thank you for purchasing the [[ plan_name ]] plan. We are excited to have you on board. You can start enjoying the benefits of your plan immediately.</p>
            <p>Plan Name: [[ plan_name ]]</p>
            <p>Plan Start Date: [[ plan_start_date ]]</p>
            <p>Plan Expiry Date: [[ plan_expiry_date ]]</p>
            <p>If you have any questions or need assistance, feel free to contact us.</p>
            <p>Best regards,</p>
            <p>[[ company_name ]]</p>',
            'subject' => 'Plan Purchase Confirmation',

            'notification_subject' => 'Your Plan Purchase is Confirmed!',
            'notification_template_detail' => '<p>Thank you for purchasing the [[ plan_name ]] plan. You can start enjoying the benefits of your plan immediately.</p>',
            'created_by' => $adminId,
        ]);

        // Admin notification template for purchase plan
        $template->defaultNotificationTemplateMap()->create([
            'language' => 'en',
            'notification_link' => '',
            'notification_message' => 'A vendor has purchased a plan.',
            'status' => 1,
            'user_type' => 'admin',
            'language' => 'en',
            'template_detail' => '<p>Dear [[ company_name ]],</p>
            <p>A vendor has purchased the [[ plan_name ]] plan. Please review the purchase details and ensure that the vendor has access to the purchased plan.</p>
            <p>Plan Name: [[ plan_name ]]</p>
            <p>Purchase Date: [[ plan_start_date ]]</p>
            <p>Vendor Name: [[ user_name ]]</p>
            <p>Best regards,</p>
            <p>[[ company_name ]]</p>',
            'subject' => 'New Plan Purchase',

            'notification_subject' => 'Vendor Plan Purchase Alert',
            'notification_template_detail' => '<p>A vendor has purchased the [[ plan_name ]] plan. Please review the purchase details and take necessary actions.</p>',
            'created_by' => $adminId,
        ]);

        // Creating the vendor registration notification template
        $template = NotificationTemplate::create([
            'type' => 'vendor_registered',
            'name' => 'vendor_registered',
            'label' => 'Vendor Registered',
            'created_by' => 1,
            'status' => 1,
            'to' => '["admin","super admin"]',
            'channels' => ['IS_MAIL' => '1', 'PUSH_NOTIFICATION' => '1', 'IS_CUSTOM_WEBHOOK' => '0'],
            'created_by' => $adminId,
        ]);

        // Vendor notification template for vendor registration
        $template->defaultNotificationTemplateMap()->create([
            'language' => 'en',
            'notification_link' => '',
            'notification_message' => 'Welcome to our platform! Your registration was successful.',
            'status' => 1,
            'user_type' => 'admin', // Changed to 'vendor'
            'template_detail' => '<p>Dear [[ user_name ]],</p>
            <p>Welcome to [[ company_name ]]! We are excited to have you on board. Your registration was successful, and you can now access all of our platform’s features.</p>
            <p>If you have any questions or need assistance, feel free to reach out to our support team.</p>
            <p>Best regards,</p>
            <p>[[ company_name ]]</p>',
            'subject' => 'Vendor Registration',

            'notification_subject' => 'Registration Successful!',
            'notification_template_detail' => '<p>Welcome to [[ company_name ]]! Your registration was successful, and you can now access all features.</p>',
            'created_by' => $adminId,
        ]);

        // Admin notification template for vendor registration
        $template->defaultNotificationTemplateMap()->create([
            'language' => 'en',
            'notification_link' => '',
            'notification_message' => 'A new vendor has registered on the platform.',
            'status' => 1,
            'user_type' => 'super admin',
            'template_detail' => '<p>Dear [[ company_name ]],</p>
            <p>A new vendor has registered on [[ company_name ]]. Please review their details and approve their account if necessary.</p>
            <p>Vendor Name: [[ user_name ]]</p>
            <p>Registration Date: [[ registration_date ]]</p>
            <p>Email: [[ user_email ]]</p>
            <p>Best regards,</p>
            <p>[[ company_name ]]</p>',
            'subject' => 'New Vendor Registration',

            'notification_subject' => 'New Vendor Registered!',
            'notification_template_detail' => '<p>A new vendor has registered on [[ company_name ]]. Please review their details.</p>',
            'created_by' => $adminId,
        ]);

        $template = NotificationTemplate::create([
            'type' => 'new_subscription',
            'name' => 'new_subscription',
            'label' => 'New Vendor Subscribed',
            'status' => 1,
            'to' => '["super admin","admin"]',
            'channels' => ['IS_MAIL' => '1', 'PUSH_NOTIFICATION' => '1', 'IS_CUSTOM_WEBHOOK' => '0'],
            'created_by' => $adminId,
        ]);
        $template->defaultNotificationTemplateMap()->create([
            'language' => 'en',
            'notification_link' => '',
            'notification_message' => 'A new vendor has subscribed',
            'status' => 1,
            'user_type' => 'super admin',
            'subject' => 'New Vendor is subscribe!',
            'notification_subject' => 'New Vendor is subscribe!',
            'notification_template_detail' => 'A new vendor has subscribed',
            'mail_subject' => 'New Subscription Plan Activated',
            'whatsapp_subject' => 'New Subscription Plan Activated',
            'sms_subject' => 'New Subscription Plan Activated',
          'template_detail' => '
        <p>Dear Super Admin,</p>
        <p><strong>[[ username ]]</strong> has subscribed to the <strong>[[ name ]]</strong> plan.</p>
        <p>Please log in to your admin dashboard to view or manage this subscription.</p>
        <p>Best regards,<br>
        [[ company_name ]]</p>
    ',
            'whatsapp_template_detail' => '<p>[[ username ]] has subscribed to a new [[ name ]].</p>',
            'sms_template_detail' => '<p>[[ username ]] has subscribed to a new [[ name ]].</p>',
            'mail_template_detail' => '<p>[[ username ]] has subscribed to a new [[ name ]].</p>',
            'created_by' => $adminId,
        ]);
        $template->defaultNotificationTemplateMap()->create([
            'language' => 'en',
            'notification_link' => '',
            'notification_message' => 'A new vendor has subscribed',
            'status' => 1,
            'user_type' => 'admin',
            'subject' => 'New Vendor is subscribe!',
            'template_detail' => 'A new vendor has subscribed',
            'notification_subject' => 'New Vendor is subscribe!',
            'notification_template_detail' => 'A new vendor has subscribed',
            'mail_subject' => 'New Subscription Plan Activated',
            'whatsapp_subject' => 'New Subscription Plan Activated',
            'sms_subject' => 'New Subscription Plan Activated',
         'template_detail' => '
<p>Hi [[ username ]],</p>

<p>Thank you for subscribing to the <strong>[[ name ]]</strong> plan!</p>

<p>Your subscription has been successfully activated. You can now enjoy all the benefits and features included in your plan.</p>

<p>If you have any questions or need support, feel free to contact us anytime.</p>

<p>Best regards,<br>
[[ company_name ]] Team</p>
',
            'whatsapp_template_detail' => '<p>[[ username ]] has subscribed to a new [[ name ]].</p>',
            'sms_template_detail' => '<p>[[ username ]] has subscribed to a new [[ name ]].</p>',
            'mail_template_detail' => '<p>[[ username ]] has subscribed to a new [[ name ]].</p>',
            'created_by' => $adminId,
        ]);

        $template = NotificationTemplate::create([
            'type' => 'cancel_subscription',
            'name' => 'cancel_subscription',
            'label' => 'Vendor Cancel Subscription',
            'status' => 1,
            'to' => '["admin","super admin"]',
            'channels' => ['IS_MAIL' => '1', 'PUSH_NOTIFICATION' => '1', 'IS_CUSTOM_WEBHOOK' => '0'],
            'created_by' => $adminId,
        ]);
        $template->defaultNotificationTemplateMap()->create([
            'language' => 'en',
            'notification_link' => '',
            'notification_message' => 'A vendor has Cancel subscription',
            'status' => 1,
            'user_type' => 'admin',
            'subject' => 'A Vendor is Cancel subscribe!',
            'template_detail' => 'A vendor has Cancel subscription',
            'notification_subject' => 'A Vendor is Cancel subscribe!',
            'notification_template_detail' => 'A vendor has Cancel subscription',
            'mail_subject' => 'New Subscription Plan Activated',
            'whatsapp_subject' => 'New Subscription Plan Activated',
            'sms_subject' => 'New Subscription Plan Activated',
            'template_detail' => '
<p>Hi [[ username ]],</p>

<p>Your subscription to the <strong>[[ name ]]</strong> plan has been successfully canceled.</p>

<p>We’re sorry to see you go! Your access to the plan features will remain active until the end of your current billing period.</p>

<p>If you change your mind or need any assistance, feel free to reach out to our support team.</p>

<p>Thank you for being with us.<br>
Best regards,<br>
[[ company_name ]] Team</p>
',
            'whatsapp_template_detail' => '<p>[[ username ]] has subscribed to a new [[ name ]].</p>',
            'sms_template_detail' => '<p>[[ username ]] has subscribed to a new [[ name ]].</p>',
            'mail_template_detail' => '<p>[[ username ]] has subscribed to a new [[ name ]].</p>',
            'created_by' => $adminId,
        ]);
        $template->defaultNotificationTemplateMap()->create([
            'language' => 'en',
            'notification_link' => '',
            'notification_message' => 'A vendor has Cancel subscription',
            'status' => 1,
            'user_type' => 'super admin',
            'subject' => 'A Vendor is Cancel subscribe!',
            'template_detail' => 'A vendor has Cancel subscription',
            'notification_subject' => 'A Vendor is Cancel subscribe!',
            'notification_template_detail' => 'A vendor has Cancel subscription',
            'mail_subject' => 'New Subscription Plan Activated',
            'whatsapp_subject' => 'New Subscription Plan Activated',
            'sms_subject' => 'New Subscription Plan Activated',
            'template_detail' => '
<p>Dear Super Admin,</p>

<p><strong>[[ username ]]</strong> has canceled their subscription to the <strong>[[ name ]]</strong> plan.</p>

<p>Please review the cancellation details in the admin dashboard if any action is needed.</p>

<p>Regards,<br>
[[ company_name ]] System</p>
',
            'whatsapp_template_detail' => '<p>[[ username ]] has Cancel subscription Plan [[ name ]].</p>',
            'sms_template_detail' => '<p>[[ username ]] has Cancel subscription Plan [[ name ]].</p>',
            'mail_template_detail' => '<p>[[ username ]] has Cancel subscription Plan [[ name ]].</p>',
            'created_by' => $adminId,
        ]);


        $template = NotificationTemplate::create([
            'type' => 'order_placed',
            'name' => 'order_placed',
            'label' => 'Order Placed',
            'status' => 1,
            'to' => '["user","admin"]',
            'channels' => ['IS_MAIL' => '1', 'PUSH_NOTIFICATION' => '1', 'IS_CUSTOM_WEBHOOK' => '0'],
            'created_by' => $adminId,
        ]);


        // Admin notification template
        $template->defaultNotificationTemplateMap()->create([
            'language' => 'en',
            'notification_link' => '',
            'notification_message' => 'Thank you for choosing Us for your recent order. We are delighted to confirm that your order has been successfully placed!',
            'status' => 1,
            'user_type' => 'admin',
            'template_detail' => '<p>Dear Admin,</p>
                <p>Thank you for choosing Us for your recent order. We are delighted to confirm that your order has been successfully placed.</p>
                <p>Best regards,</p>
                <p>&nbsp;</p>
                <p>[[ company_name ]]</p>',
            'subject' => 'Order Placed!',
           'notification_subject' => 'Order Confirmation',
                'notification_template_detail' => '<p>We are delighted to confirm that your order has been successfully placed.</p>',
            'created_by' => $adminId,

        ]);

        // User notification template
        $template->defaultNotificationTemplateMap()->create([
            'language' => 'en',
            'notification_link' => '',
            'notification_message' => 'Thank you for placing your order with us! We are processing it and will notify you once it\'s ready to be shipped.',
            'status' => 1,
            'user_type' => 'user',
            'template_detail' => '<p>Dear Admin,</p>
            <p>&nbsp;</p>
            <p>Thank you for choosing Us for your recent order. We are delighted to confirm that your order has been successfully placed.</p>
            <p>&nbsp;</p>
            <p>Best regards,</p>
            <p>&nbsp;</p>
            <p>[[ company_name ]]</p>',
            'subject' => 'Your Order has been Placed!',
            'notification_subject' => 'Order Confirmation',
            'notification_template_detail' => '<p>We are delighted to confirm that your order has been successfully placed.</p>',
            'created_by' => $adminId,
        ]);


        $template = NotificationTemplate::create([
            'type' => 'order_proccessing',
            'name' => 'order_proccessing',
            'label' => 'Order Processing',
            'status' => 1,
            'to' => '["user","admin"]',
            'channels' => ['IS_MAIL' => '1', 'PUSH_NOTIFICATION' => '1', 'IS_CUSTOM_WEBHOOK' => '0'],
            'created_by' => $adminId,
        ]);

        // User notification template
        $template->defaultNotificationTemplateMap()->create([
            'language' => 'en',
            'notification_link' => '',
            'notification_message' => "We're excited to let you know that your order is now being prepared and will soon be on its way to satisfy your taste buds!",
            'status' => 1,
            'user_type' => 'user',
            'template_detail' => '<p>Dear [[ user_name ]],</p>
            <p>&nbsp;</p>
            <p>We\'re excited to let you know that your order is now being prepared and will soon be on its way to satisfy your taste buds!</p>
            <p>&nbsp;</p>
            <p>Thank you for choosing us. We hope you enjoy your meal!</p>
            <p>&nbsp;</p>
            <p>Best regards,</p>
            <p>&nbsp;</p>
            <p>&nbsp;</p>
            <p>[[ company_name ]]</p>',
                            'subject' => 'Order Processing!',
            'notification_subject' => 'Your Order is Being Prepared',
            'notification_template_detail' => '<p>We\'re excited to let you know that your order is now being prepared and will soon be on its way to satisfy your taste buds!</p>',
            'created_by' => $adminId,
            ]);

        // Admin notification template
        $template->defaultNotificationTemplateMap()->create([
            'language' => 'en',
            'notification_link' => '',
            'notification_message' => "An order is currently being processed and will soon be ready for dispatch.",
            'status' => 1,
            'user_type' => 'admin',
            'template_detail' => '<p>Dear [[ admin_name ]],</p>
            <p>An order is currently being processed and will soon be ready for dispatch.</p>
            <p>Order ID: [[ order_id ]]</p>
            <p>Order Date: [[ order_date ]]</p>
            <p>Customer Name: [[ user_name ]]</p>
            <p>Best regards,</p>
            <p>[[ company_name ]]</p>',
                            'subject' => 'Order Processing!',
                            'notification_subject' => 'Order Processing Notification',
                            'notification_template_detail' => '<p>An order is currently being processed and will soon be ready for dispatch.</p>
            <p>&nbsp;</p>',
            'created_by' => $adminId,
        ]);

        $template = NotificationTemplate::create([
            'type' => 'order_delivered',
            'name' => 'order_delivered',
            'label' => 'Order Delivered',
            'status' => 1,
            'to' => '["user","admin"]',
            'channels' => ['IS_MAIL' => '1', 'PUSH_NOTIFICATION' => '1', 'IS_CUSTOM_WEBHOOK' => '0'],
            'created_by' => $adminId,
        ]);

        // User notification template
        $template->defaultNotificationTemplateMap()->create([
            'language' => 'en',
            'notification_link' => '',
            'notification_message' => "We're delighted to inform you that your order has been successfully delivered to your doorstep.",
            'status' => 1,
            'user_type' => 'user',
            'template_detail' => '<p>Dear [[ user_name ]],</p>
            <p>We\'re delighted to inform you that your order has been successfully delivered to your doorstep.</p>
            <p>Order ID: [[ order_id ]]</p>
            <p>Order Date: [[ order_date ]]</p>
            <p>If you have any questions or need further assistance, please feel free to contact us.</p>
            <p>Best regards,</p>
            <p>[[ company_name ]]</p>',
                            'subject' => 'Your Order is Delivered!',
                            'notification_subject' => 'Order Delivery Confirmation',
                            'notification_template_detail' => '<p>We\'re delighted to inform you that your order has been successfully delivered to your doorstep.</p>',
            'created_by' => $adminId,
        ]);

        // Admin notification template
        $template->defaultNotificationTemplateMap()->create([
            'language' => 'en',
            'notification_link' => '',
            'notification_message' => "An order has been delivered to the customer.",
            'status' => 1,
            'user_type' => 'admin',
            'template_detail' => '<p>Dear [[ admin_name ]],</p>
            <p>An order has been delivered to the customer.</p>
            <p>Order ID: [[ order_id ]]</p>
            <p>Order Date: [[ order_date ]]</p>
            <p>Customer Name: [[ user_name ]]</p>
            <p>Delivery Address: [[ delivery_address ]]</p>
            <p>Best regards,</p>
            <p>[[ company_name ]]</p>',
            'subject' => 'Order Delivered!',
            'notification_subject' => 'Order Delivery Notification',
            'notification_template_detail' => '<p>An order has been delivered to the customer.</p>',
            'created_by' => $adminId,
        ]);


        $template = NotificationTemplate::create([
            'type' => 'order_cancelled',
            'name' => 'order_cancelled',
            'label' => 'Order Cancelled',
            'status' => 1,
            'to' => '["user","admin"]',
            'channels' => ['IS_MAIL' => '1', 'PUSH_NOTIFICATION' => '1', 'IS_CUSTOM_WEBHOOK' => '0'],
            'created_by' => $adminId,
        ]);

        // User notification template
        $template->defaultNotificationTemplateMap()->create([
            'language' => 'en',
            'notification_link' => '',
            'notification_message' => 'We regret to inform you that your recent order has been cancelled.',
            'status' => 1,
            'user_type' => 'user',
            'template_detail' => '<p>Dear [[ user_name ]],</p>
            <p>We regret to inform you that your recent order has been cancelled. Please contact us if you have any questions or need further assistance.</p>
            <p>Order ID: [[ order_id ]]</p>
            <p>Order Date: [[ order_date ]]</p>
            <p>Best regards,</p>
            <p>[[ company_name ]]</p>',
            'subject' => 'Order Cancelled!',
            'notification_subject' => 'Order Cancellation Notification',
            'notification_template_detail' => '<p>We regret to inform you that your recent order has been cancelled. Please contact us if you have any questions or need</p>',
            'created_by' => $adminId,
        ]);

        // Admin notification template
        $template->defaultNotificationTemplateMap()->create([
            'language' => 'en',
            'notification_link' => '',
            'notification_message' => 'An order has been cancelled by the user.',
            'status' => 1,
            'user_type' => 'admin',
            'language' => 'en',
            'template_detail' => '<p>Dear [[ admin_name ]],</p>
                <p>An order has been cancelled by the user. Please review the order details and take any necessary actions.</p>
                <p>Order ID: [[ order_id ]]</p>
                <p>Order Date: [[ order_date ]]</p>
                <p>Customer Name: [[ user_name ]]</p>
                <p>Best regards,</p>
                <p>[[ company_name ]]</p>',
            'subject' => 'Order Cancelled!',
            'notification_subject' => 'Order Cancellation Alert',
            'notification_template_detail' => '<p>An order has been cancelled by the user. Please review the order details and take any necessary actions.</p>',

        ]);
    }
}
