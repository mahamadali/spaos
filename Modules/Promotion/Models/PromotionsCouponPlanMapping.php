<?php

namespace Modules\Promotion\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Subscriptions\Models\Plan;

class PromotionsCouponPlanMapping extends Model
{
    use HasFactory;
    protected $table = 'promotions_coupon_plan_mappings';

    protected $fillable = [
        'coupon_id',
        'plan_id',
    ];


    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id');  
    }
}