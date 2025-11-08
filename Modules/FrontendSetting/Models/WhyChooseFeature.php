<?php

namespace Modules\FrontendSetting\Models;

use App\Models\BaseModel;

class WhyChooseFeature extends BaseModel
{
    protected $table = 'why_choose_features';
    protected $fillable = [
        'why_choose_id',
        'title',
        'subtitle',
        'image',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
