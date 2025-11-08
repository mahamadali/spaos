<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Currency\Models\Currency;

class DefaultCurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currency = Currency::where('currency_code','INR')->where('created_by',1)->first();
        $currency = $currency ?? New Currency();

        $currency->currency_name  = 'Rupee';
        $currency->currency_symbol  = '₹';
        $currency->currency_code  = 'INR';
        $currency->is_primary  = true;
        $currency->no_of_decimal  = 2;
        $currency->save();
    }
}
