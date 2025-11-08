<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\BaseModel;

class Faq extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'question',
        'answer',
        'status',
    ];
}
