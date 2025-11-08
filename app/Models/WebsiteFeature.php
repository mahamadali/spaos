<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebsiteFeature extends Model
{
    use HasFactory;

    protected $fillable = [
        'website_setting_id',
        'title',
        'description',
        'image',
    ];
}
