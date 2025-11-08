<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Subscriptions\Models\Plan;
use Modules\Subscriptions\Models\Subscription;
use App\Models\BaseModel;

class Payment extends BaseModel
{
    use HasFactory;

    const CUSTOM_FIELD_MODEL = 'App\Models\Payment';

    protected $fillable = [
        'user_id',
        'plan_id',
        'subscription_id',
        'amount',
        'payment_method',
        'payment_date',
        'image',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'id');
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class, 'subscription_id', 'id');
    }
}
