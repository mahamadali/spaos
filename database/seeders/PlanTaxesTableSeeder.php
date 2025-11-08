<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PlanTax;
use Modules\Subscriptions\Models\Plan;

class PlanTaxesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('plan_taxes')->delete();
        
        $taxes = [
            [
                'id' => 1,
                'title' => 'VAT',
                'type' => 'Percentage',
                'value' => 5.0,
                'plan_ids' => '2,3,6',
                'status' => 1,
                'created_by' => 1,
                'updated_by' => 1,
                'deleted_by' => NULL,
                'created_at' => '2025-01-28 08:15:07',
                'updated_at' => '2025-01-28 08:15:07',
                'deleted_at' => NULL,
            ],
            [ 
                'id' => 2,
                'title' => 'Service Tax',
                'type' => 'Fixed',
                'value' => 15.0,
                'plan_ids' => '4,5,3',
                'status' => 1,
                'created_by' => 1,
                'updated_by' => 1,
                'deleted_by' => NULL,
                'created_at' => '2025-01-28 08:17:02',
                'updated_at' => '2025-01-28 08:17:02',
                'deleted_at' => NULL,
            ],
        ];
        
        foreach ($taxes as $taxData) {
            PlanTax::create($taxData);
        }

        $planTaxes = PlanTax::all();

        foreach ($planTaxes as $tax) {
            $planIds = explode(',', $tax->plan_ids);  

            $plans = Plan::whereIn('id', $planIds)->get();

            foreach ($plans as $plan) {
                $plan->tax = $plan->calculateTotalTax();
                $plan->total_price = $plan->totalPrice();
                $plan->save();
            }
        }
    }
}