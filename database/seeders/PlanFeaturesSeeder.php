<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanFeaturesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data for plan_features table
        $features = [
            ['plan_id' => 2, 'title' => 'Online Booking'],
            ['plan_id' => 2, 'title' => 'Priority Support'],
            ['plan_id' => 2, 'title' => 'Priority Support'],
            ['plan_id' => 2, 'title' => 'Priority Support'],
            ['plan_id' => 3, 'title' => 'Custom Reports'],
            ['plan_id' => 3, 'title' => 'Advanced Analytics'],
            ['plan_id' => 4, 'title' => 'Yearly Discounts'],
            ['plan_id' => 4, 'title' => 'Unlimited Bookings'],
            ['plan_id' => 5, 'title' => 'Team Collaboration'],
            ['plan_id' => 5, 'title' => 'Data Export'],
        ];

        foreach ($features as $feature) {
            DB::table('plan_features')->updateOrInsert(
                [
                    'plan_id' => $feature['plan_id'],
                    'title' => $feature['title'],
                ]
            );
        }
    }
}
