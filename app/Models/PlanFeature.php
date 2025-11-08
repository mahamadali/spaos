<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanFeature extends Model
{
    use HasFactory;

    public $timestamps = false;  // Disable timestamps if you don't need them

    protected $fillable = [
        'plan_id',
        'title',
    ];
}
