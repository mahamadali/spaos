<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class VendorScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        // Check if vendor is set in app instance
        if (app()->bound('active_vendor') && app('active_vendor')) {
            $vendor = app('active_vendor');
            $builder->where($model->getTable() . '.created_by', $vendor->id);
        }elseif (auth()->check() && auth()->user()->user_type == 'admin') {
            $builder->where($model->getTable() . '.created_by', auth()->id());
        } 
    }
}
