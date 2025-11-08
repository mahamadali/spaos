<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebsiteSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'website_title',
        'website_logo',
        'facebook_link',
        'instagram_link',
        'youtube_link',
        'twitter_link',
        'about_us',
        'status',
    ];

    public function features()
    {
        return $this->hasMany(WebsiteFeature::class);
    }

    public function homepages()
    {
        return $this->hasMany(WebsiteHomepage::class);
    }
}
