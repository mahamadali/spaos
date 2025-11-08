<?php

namespace Modules\Promotion\Transformers;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class PromotionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'coupon_image' => $this->feature_image,
            'coupon_code' => optional($this->coupon)->coupon_code,
            'coupon_type' => optional($this->coupon)->coupon_type,
            'start_date_time' => optional($this->coupon)->start_date_time,
            'end_date_time' => optional($this->coupon)->end_date_time,
            'is_expired' => optional($this->coupon)->is_expired,
            'discount_type' => optional($this->coupon)->discount_type,
            'discount_percentage' => optional($this->coupon)->discount_percentage,
            'discount_amount' => optional($this->coupon)->discount_amount,
            'Select_Plan' => optional($this->coupon)->Select_Plan,
            'used_by' => optional($this->coupon)->used_by,
            'promotion_id' => optional($this->coupon)->promotion_id,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_by' => $this->deleted_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
