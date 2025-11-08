<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebsiteHomepage extends Model
{
    protected $fillable = ['website_setting_id','key', 'value'];

    use HasFactory;
}
