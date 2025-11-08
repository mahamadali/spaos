<?php

namespace Modules\Subscriptions\Models;

use App\Jobs\BulkNotification;
use App\Models\BaseModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'plan_id',
        'user_id',
        'transaction_id',
        'amount',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'currency',
        'currency_symbol',
        'payment_method',
        'gateway_type',
        'start_date',
        'end_date',
        'status',
        'plan_details',
        'gateway_response',
        'is_active',
        'max_appointment',
        'max_branch',
        'max_service',
        'max_staff',
        'max_customer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function subscription_transaction()
    {
        return $this->hasOne(SubscriptionTransactions::class, 'subscriptions_id', 'id');
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'id');
    }


    public function sendNotificationOnPlanPurchase()
    {
        $type = 'purchase_plan';

        // Update message template to reflect plan purchase
        $messageTemplate = 'New plan #[[plan_name]] has been purchased by [[user_name]].';

        // Replace placeholders with actual data
        $notify_message = str_replace('[[plan_name]]', $this->plan->title, $messageTemplate);
        $notify_message = str_replace('[[user_name]]', $this->user->getFullNameAttribute(), $notify_message);

        // Assuming mail_footer function provides the required footer for the email
        $data = mail_footer($type, $notify_message);

        // Include order details in the data
        $data['plan_name'] = $this->plan->title;
        $data['user_name'] = $this->user->getFullNameAttribute();
        $data['user_id'] = 1;
        $data['plan_start_date'] = $this->start_date;
        $data['plan_expiry_date'] = $this->end_Date;
        $data['company_name'] = env('APP_NAME');

        // Dispatch notification
        BulkNotification::dispatch($data);
    }

    public function startDate()
    {
        // Get the most recent active subscription
        $previousSubscription = Subscription::where('user_id', $this->user_id)->where('id','!=',$this->id)
                                            ->where('is_active', 1)
                                            ->first();

        if ($previousSubscription) {
            // If the previous subscription is active, set start date to the next day after the previous subscription's end date
            $start_date = Carbon::now();
        } else {
            // If no active previous subscription, start date is now
            $this->is_active = 1;
            $this->save();
            $start_date = Carbon::now();
        }

        return $start_date;
    }

    // End date logic
    public function endDate()
    {
        // Get the start date for the current subscription
        $start_date = $this->startDate();

        // Calculate the end date based on the plan type and duration
        if ($this->plan->type == 'Monthly') {
            $end_date = $start_date->copy()->addMonths($this->plan->duration);
        } elseif ($this->plan->type == 'Yearly') {
            $end_date = $start_date->copy()->addYears($this->plan->duration);
        } elseif ($this->plan->type == 'Weekly') {
            $end_date = $start_date->copy()->addWeeks($this->plan->duration);
        } elseif ($this->plan->type == 'Daily') {
            $end_date = $start_date->copy()->addDays($this->plan->duration);
        }

        return $end_date;
    }

    public function cancelExistingPlan($user_id)
    {
        // Get the user's active subscription
        $activeSubscription = self::where('user_id', $user_id)
            ->where('status', config('constant.SUBSCRIPTION_STATUS.ACTIVE'))
            ->where('is_active', 1)
            ->first();

        if ($activeSubscription) {
            // Cancel the existing subscription
            $activeSubscription->status = config('constant.SUBSCRIPTION_STATUS.INACTIVE');
            $activeSubscription->is_active = 0;
            $activeSubscription->save();
            
            // Update user's subscription status if needed
            $user = User::find($user_id);
            if (!$user->subscriptions()->where('status', config('constant.SUBSCRIPTION_STATUS.ACTIVE'))->exists()) {
                $user->is_subscribe = 0;
                $user->save();
            }
            
            return true;
        }
        
        return false;
    }

}
