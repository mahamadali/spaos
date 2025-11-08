<?php

namespace Modules\Subscriptions\Models;

use App\Models\BaseModel;
use App\Models\Permission;
use App\Models\PlanFeature;
use App\Models\PlanTax;
use App\Models\User;
use App\Models\UserHasMenuPermission;
use App\Trait\CustomFieldsTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\MenuBuilder\Models\MenuBuilder;

class Plan extends BaseModel
{
    use CustomFieldsTrait,HasFactory,SoftDeletes;

    protected $table = 'plan';

    protected $fillable = [
        'name',
        'type',
        'identifier',
        'duration',
        'price',
        'tax',
        'total_price',
        'currency',
        'permission_ids',
        'description',
        'status',
        'max_appointment',
        'max_branch',
        'max_service',
        'max_staff',
        'max_customer',
        'is_free_plan',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    const CUSTOM_FIELD_MODEL = 'Modules\Subscriptions\Models\Plan';
 
    public function delete()
    {
        PlanFeature::where('plan_id', $this->id)->delete();
        Plan::where('id',$this->id)->delete();
        return;
    }

    public function features()
    {
        return $this->hasMany(PlanFeature::class);
    }

    public function planLimitation()
    {
        return $this->hasMany(PlanLimitationMapping::class, 'plan_id', 'id')->with('limitation_data');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by', 'id')->withTrashed();
    }

    public function taxes()
    {
        return PlanTax::where(function($query) {
            $query->whereNotNull('plan_ids')
                ->whereRaw('FIND_IN_SET(?, plan_ids)', [$this->id]);
        })->where('status',1)->get();
    }

    public function calculateTotalTax()
    {
        $totalTax = 0;
        // Determine the base price for tax calculation
        $basePrice = $this->has_discount ? $this->discounted_price : $this->price;

        foreach ($this->taxes() as $tax) {
            if ($tax->type === 'Fixed') {
                $totalTax += $tax->value; // Add fixed tax value
            } elseif ($tax->type === 'Percentage') {
                $totalTax += ($tax->value / 100) * $basePrice; // Calculate percentage tax on discounted price if applicable
            }
        }

        return $totalTax;
    }

    public function totalPrice()
    {
        $totalTax = $this->calculateTotalTax();
        $basePrice = $this->has_discount ? $this->discounted_price : $this->price;
        return $basePrice + $totalTax;
    }


public function givePermissionToUser($user_id)
{
    // Remove existing permissions for the user
    UserHasMenuPermission::where('user_id', $user_id)->delete();

    // Define the free plan permissions
    $free_plan = [
        'view_branch', 'add_branch', 'edit_branch', 'delete_branch', 'branch_gallery',
        'view_dashboard', 'add_dashboard', 'edit_dashboard', 'delete_dashboard',
        'view_booking', 'add_booking', 'edit_booking', 'delete_booking', 'booking_booking_tableview',
        'view_service', 'add_service', 'edit_service', 'delete_service', 'service_gallery',
        'view_category', 'add_category', 'edit_category', 'delete_category',
        'view_package', 'add_package', 'edit_package', 'delete_package',
        'view_subcategory', 'add_subcategory', 'edit_subcategory', 'delete_subcategory',
        'view_promotion', 'add_promotion', 'edit_promotion', 'delete_promotion',
        'view_staff', 'add_staff', 'edit_staff', 'delete_staff', 'staff_password',
        'view_customer', 'add_customer', 'edit_customer', 'delete_customer', 'customer_password',
        'view_page', 'add_page', 'edit_page', 'delete_page',
        'view_tax', 'add_tax', 'edit_tax', 'delete_tax',
        'view_review', 'add_review', 'edit_review', 'delete_review',
        'view_setting', 'setting_general', 'setting_misc', 'setting_quick_booking',
        'setting_customization', 'setting_mail', 'setting_currency', 'setting_commission',
        'setting_holiday', 'setting_bussiness_hours', 'setting_language',
        'view_product', 'add_product', 'edit_product', 'delete_product', 'product_stock', 'product_gallary',
        'view_product_variations', 'add_product_variations', 'edit_product_variations', 'delete_product_variations',
        'view_product_category', 'add_product_category', 'edit_product_category', 'delete_product_category',
        'view_product_brand', 'add_product_brand', 'edit_product_brand', 'delete_product_brand',
        'view_product_units', 'add_product_units', 'edit_product_units', 'delete_product_units',
        'view_product_tags', 'add_product_tags', 'edit_product_tags', 'delete_product_tags',
        'view_logistics', 'add_logistics', 'edit_logistics', 'delete_logistics',
        'view_shipping_zone', 'add_shipping_zone', 'edit_shipping_zone', 'delete_shipping_zone',
        'view_product_orders', 'add_product_orders', 'edit_product_orders', 'delete_product_orders',
        'view_staff_earning', 'add_staff_earning', 'edit_staff_earning', 'delete_staff_earning'
    ];

    // Determine permissions based on the user's plan
    $permission_names = $this->is_free_plan ? $free_plan : $this->permission_ids;

    // Ensure permission_names is an array
    if (is_string($permission_names)) {
        $decoded_permissions = json_decode($permission_names, true);
        $permission_names = json_last_error() === JSON_ERROR_NONE ? $decoded_permissions : explode(',', $permission_names);
    } elseif (!is_array($permission_names)) {
        $permission_names = [];
    }

    // Clean permission names
    $permission_names = array_map(fn($perm) => trim($perm, '[]"'), $permission_names);

    
    $menus = MenuBuilder::where(function ($query) use ($permission_names) {
        foreach ($permission_names as $permission) {
            $query->orWhereJsonContains('permission', $permission);
        }
    })
    ->get(['id', 'parent_id']);  // Fetch both 'id' and 'parent_id'



    // Batch insert user permissions for better performance
    if ($menus->count() > 0) {
        $permissionData = [];
        foreach ($menus as $menu) {
            $permissionData[] = [
                'user_id' => $user_id,
                'menu_id' => $menu->id,
                'parent_id' => $menu->parent_id ?? 0,
            ];
        }
        UserHasMenuPermission::insert($permissionData);
    }

}

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

}
