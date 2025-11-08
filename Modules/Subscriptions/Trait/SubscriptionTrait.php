<?php

namespace Modules\Subscriptions\Trait;

use App\Models\User;
use Modules\Subscriptions\Models\Plan;
use Modules\Subscriptions\Models\Subscription;
use Modules\Subscriptions\Transformers\SubscriptionResource;
use App\Models\Payment;
use App\Jobs\BulkNotification;

trait SubscriptionTrait
{
    public function getTimeZone()
    {
        $timezone = \App\Models\Setting::first();

        return $timezone->time_zone ?? 'UTC';
    }

    public function get_user_active_plan($user_id)
    {
        $get_provider_plan = Subscription::where('user_id', $user_id)->where('status', config('constant.SUBSCRIPTION_STATUS.ACTIVE'))->first();
        $activeplan = null;
        if (! empty($get_provider_plan)) {
            $activeplan = new SubscriptionResource($get_provider_plan);
        }

        return $activeplan;
    }

    public function check_days_left_plan($old_plan)
    {
        $previous_plan_start = $old_plan->start_date;
        $previous_plan_end = new \Carbon\Carbon($old_plan->end_date);
        $new_plan_start = new \Carbon\Carbon(date('Y-m-d H:i:s'));
        $left_days = $previous_plan_end->diffInDays($new_plan_start);

        return $left_days;
    }

    public function get_plan_expiration_date($plan_start_date = '', $plan_type = '', $plan_duration = 1)
{



    $left_days = 0;
    $start_at = new \Carbon\Carbon($plan_start_date);

    $end_date = null; // Initialize as null to avoid potential string-related errors.

    if ($plan_type === 'Monthly') {
        $end_date = $start_at->addMonths($plan_duration)->addDays($left_days);
    } elseif ($plan_type === 'Yearly') {
        $end_date = $start_at->addYears($plan_duration)->addDays($left_days);
    } elseif ($plan_type === 'Weekly') {
        $getdays = Plan::where('identifier', 'free')->first();
        $getdays = $getdays ? $getdays->duration*7 : 0; // Avoid errors if no result is found.
        $days = $left_days + $getdays;
        $end_date = $start_at->addDays($days);
    }
    if($end_date != null){
        return $end_date->format('Y-m-d H:i:s');
    }
}


    public function send_mail($user_id)
    {
        try {
            $user = User::where('id', $user_id)->first();

            $subject = 'Your Trail Subscribe Plan will expire in 2 Days';
            $message = "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.";

            \Mail::send('subscription.subscription_email',
                [
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'email' => $user['email'],
                    'subject' => $subject,
                    'phone_no' => $user['phone_no'],
                    'message' => $message,
                ], function ($message) use ($user) {
                    $message->from($user->email);
                    $message->to(env('MAIL_FROM_ADDRESS'));
                });

            return $messagedata = __('messages.contact_us_greetings');
        } catch (\Exception $e) {
            \Log::error($e->getMessage());

            return $messagedata = __('messages.something_wrong');
        }
    }


    public function StorepaymentData($user_id,$plan_id, $amount, $currency,$subscription_id){


            try {
                return Payment::create([
                    'user_id'         => $user_id,
                    'plan_id'         => $plan_id,
                    'amount'          => $amount,
                    'currency'        => $currency,
                    'payment_method'  => 2,
                    'payment_date'    => now(),
                    'subscription_id' => $subscription_id,
                    'status'          => 1
                ]);
            } catch (\Exception $e) {
                \Log::error('Payment Store Error: ' . $e->getMessage());
                return null;
            }
        }
        public function sendNotificationOnsubscription($type, $notify_message, $response, $notify = true){

            $data = mail_footer($type, $notify_message, $response);

            $data['subscription'] = $response;

            BulkNotification::dispatch($data);
        }

}
