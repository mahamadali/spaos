<?php

namespace Modules\Subscriptions\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
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
            'id'                => $this->id,
            'plan_id'           => $this->plan_id,
            'name'             => $this->plan->name ?? '-',
            'amount'            => $this->amount,
            'txn_id'            => $this->transaction_id ?? '-',
            'status'            => $this->status,
            'start_at'          => $this->start_date,
            'end_at'            => $this->end_date,
            'duration'          => $this->plan->duration,
            'description'       => $this->description,
            'plan_type'         => $this->plan->type,
            'username'          => $this->user->name,
        ];
    }
}
