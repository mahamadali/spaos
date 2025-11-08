<?php

namespace Modules\Promotion\database\seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PromotionDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {


        \DB::table('promotions')->delete();

       // Insert data into promotions table
        $promotions = [
            [
                'id' => 1,
                'name' => 'FestiveSpecial',
                'description' => 'Holiday season special',
                'start_date_time' => Carbon::parse('2025-01-01'),
                'end_date_time' => Carbon::parse('2025-02-20'),
                'status' => 1, // Active
                'created_by' => 1,
                'updated_by' => 1,
                'deleted_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'name' => 'SummerDeal',
                'description' => 'Special summer promotion',
                'start_date_time' => Carbon::parse('2025-01-10'),
                'end_date_time' => Carbon::parse('2025-02-28'),
                'status' => 1, // Active
                'created_by' => 1,
                'updated_by' => 1,
                'deleted_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 3,
                'name' => 'AnnualSavings',
                'description' => 'Save on yearly subscriptions',
                'start_date_time' => Carbon::parse('2025-01-01'),
                'end_date_time' => Carbon::parse('2025-03-31'),
                'status' => 1, // Active
                'created_by' => 1,
                'updated_by' => 1,
                'deleted_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ];
        \DB::table('promotions')->insert($promotions);


    }
}


