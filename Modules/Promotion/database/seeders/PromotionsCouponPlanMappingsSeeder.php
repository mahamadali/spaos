<?php

namespace Modules\Promotion\database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PromotionsCouponPlanMappingsSeeder extends Seeder
{
    public function run()
    {
        // Clear existing data
        DB::table('promotions_coupon_plan_mappings')->truncate();

        // Insert data
        DB::table('promotions_coupon_plan_mappings')->insert([
            [
                'id' => 1,
                'coupon_id' => 1,
                'plan_id' => 2, // Basic
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'coupon_id' => 1,
                'plan_id' => 6, // Standard
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 3,
                'coupon_id' => 2,
                'plan_id' => 3, // Premium
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 4,
                'coupon_id' => 2,
                'plan_id' => 2, // Basic
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 5,
                'coupon_id' => 2,
                'plan_id' => 4, // Elite
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 6,
                'coupon_id' => 3,
                'plan_id' => 5, // Super Elite
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);
    }
}
