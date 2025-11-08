<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomepageSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'banner_image1',
        'banner_image2',
        'banner_image3',
        'banner_title',
        'banner_subtitle',
        'banner_badge_text',
        'banner_link',
        'about_title',
        'about_subtitle',
        'about_description',
        'video',
        'video_type',
        'video_url',
        'chooseUs_image',
        'chooseUs_title',
        'chooseUs_subtitle',
        'choose_us_feature_list',
        'chooseUs_description',
    ];

}
