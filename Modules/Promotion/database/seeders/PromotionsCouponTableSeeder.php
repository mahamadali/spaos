<?php

namespace Modules\Promotion\database\seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PromotionsCouponTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('promotions_coupon')->delete();
        $coupons = [
            [
                'coupon_code' => 'FESTIVE50',
                'coupon_type' => 'custom',
                'start_date_time' => Carbon::parse('2025-01-01'),
                'end_date_time' => Carbon::parse('2025-02-20'),
                'is_expired' => 0, // No
                'discount_type' => 'percent',
                'discount_percentage' => 10.0,
                'discount_amount' => 0,
                'used_by' => null,
                'promotion_id' => 1, // FestiveSpecial
                'created_by' => 1,
                'updated_by' => 1,
                'deleted_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'coupon_code' => 'SUMMER25',
                'coupon_type' => 'custom',
                'start_date_time' => Carbon::parse('2025-01-10'),
                'end_date_time' => Carbon::parse('2025-02-28'),
                'is_expired' => 0, // No
                'discount_type' => 'fixed',
                'discount_percentage' => 0,
                'discount_amount' => 15.0,
                'used_by' => null,
                'promotion_id' => 2, // SummerDeal
                'created_by' => 1,
                'updated_by' => 1,
                'deleted_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'coupon_code' => 'ANNUAL20',
                'coupon_type' => 'custom',
                'start_date_time' => Carbon::parse('2025-01-01'),
                'end_date_time' => Carbon::parse('2025-03-31'),
                'is_expired' => 0, // No
                'discount_type' => 'percent',
                'discount_percentage' => 25.0,
                'discount_amount' => 0,
                'used_by' => null,
                'promotion_id' => 3, // AnnualSavings
                'created_by' => 1,
                'updated_by' => 1,
                'deleted_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ];
        \DB::table('promotions_coupon')->insert($coupons);

    }
}
