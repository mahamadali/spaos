<?php

namespace Modules\FrontendSetting\Models;

use App\Models\BaseModel;

class WhyChoose extends BaseModel
{
    protected $table = 'why_choose';
    protected $fillable = [
        'image',
        'title',
        'subtitle',
        'description',
        'features',
        'created_by',
        'updated_by',
    ];
    protected $casts = [
        'features' => 'array',
    ];

    public function features()
    {
        return $this->hasMany(WhyChooseFeature::class, 'why_choose_id');
    }
}
