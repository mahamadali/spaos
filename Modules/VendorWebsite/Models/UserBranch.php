<?php

namespace Modules\VendorWebsite\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\VendorWebsite\Database\factories\UserBranchFactory;

class UserBranch extends Model
{
    use HasFactory;

    protected $table = 'user_branch';


    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['user_id', 'branch_id'];
    
    protected static function newFactory(): UserBranchFactory
    {
        //return UserBranchFactory::new();
    }
}
