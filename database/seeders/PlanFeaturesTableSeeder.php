<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PlanFeaturesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('plan_features')->delete();
        
        \DB::table('plan_features')->insert(array (
            0 => 
            array (
                'plan_id' => 3,
                'title' => 'Appointment Booking',
            ),
            1 => 
            array (
                'plan_id' => 3,
                'title' => 'Online & Offline Payment',
            ),
            2 => 
            array (
                'plan_id' => 3,
                'title' => 'Multi Branch Support',
            ),
            3 => 
            array (
                'plan_id' => 3,
                'title' => 'Analytics Dashboard',
            ),
            4 => 
            array (
                'plan_id' => 3,
                'title' => 'Staff Management',
            ),
            5 => 
            array (
                'plan_id' => 6,
                'title' => 'Appointment Booking',
            ),
            6 => 
            array (
                'plan_id' => 6,
                'title' => 'Multi Payment Support',
            ),
            7 => 
            array (
                'plan_id' => 6,
                'title' => 'Multi Branch Support',
            ),
            8 => 
            array (
                'plan_id' => 6,
                'title' => 'Analytics Reports',
            ),
            9 => 
            array (
                'plan_id' => 6,
                'title' => 'Staff Payout and History',
            ),
            10 => 
            array (
                'plan_id' => 4,
                'title' => 'Appointment Bookings',
            ),
            11 => 
            array (
                'plan_id' => 4,
                'title' => 'Calendar View',
            ),
            12 => 
            array (
                'plan_id' => 4,
                'title' => 'Tax and Finance Management',
            ),
            13 => 
            array (
                'plan_id' => 4,
                'title' => 'Location Management',
            ),
            14 => 
            array (
                'plan_id' => 4,
                'title' => 'Staff Payouts and History',
            ),
            15 => 
            array (
                'plan_id' => 5,
                'title' => 'Unlimited Bookings',
            ),
            16 => 
            array (
                'plan_id' => 5,
                'title' => 'Multi Payments Support',
            ),
            17 => 
            array (
                'plan_id' => 5,
                'title' => 'Multi Branch Support',
            ),
            18 => 
            array (
                'plan_id' => 5,
                'title' => 'Location Management',
            ),
            19 => 
            array (
                'plan_id' => 5,
                'title' => 'Analytics Report',
            ),
            20 => 
            array (
                'plan_id' => 5,
                'title' => 'Advanced Settings',
            ),
            21 => 
            array (
                'plan_id' => 7,
                'title' => 'Unlimited Bookings',
            ),
            22 => 
            array (
                'plan_id' => 7,
                'title' => 'Multi Payments Support',
            ),
            23 => 
            array (
                'plan_id' => 7,
                'title' => 'Multi Branch Support',
            ),
            24 => 
            array (
                'plan_id' => 7,
                'title' => 'Coupon and Discounts',
            ),
            25 => 
            array (
                'plan_id' => 7,
                'title' => 'Location Management',
            ),
            26 => 
            array (
                'plan_id' => 7,
                'title' => 'Staff Payout and History',
            ),
            27 => 
            array (
                'plan_id' => 7,
                'title' => 'Packages',
            ),
            28 => 
            array (
                'plan_id' => 2,
                'title' => 'Appointment Booking',
            ),
            29 => 
            array (
                'plan_id' => 2,
                'title' => 'Support Offline Payment only',
            ),
            30 => 
            array (
                'plan_id' => 2,
                'title' => 'Support Single Branch',
            ),
            31 => 
            array (
                'plan_id' => 2,
                'title' => 'Calendar View',
            ),
            32 => 
            array (
                'plan_id' => 2,
                'title' => 'Payout Reports',
            ),
        ));
        
        
    }
}