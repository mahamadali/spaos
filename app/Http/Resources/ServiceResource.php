<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {


        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'service_image' => $this->media->pluck('original_url')->first(),
            'duration_min' => $this->duration_min,
            'default_price' => $this->default_price,
            // 'branch_service_price' => $this->branches->service_price ??$this->default_price,
            'category_id' => $this->category_id,
            'sub_category_id' => $this->sub_category_id,
            'status' => $this->status,
          
            
        ];
    }
}
