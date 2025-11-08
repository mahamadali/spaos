<?php

namespace Modules\Currency\database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Currency\Models\Currency;

class CurrencyDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $data = [
            [
                'currency_name' => 'Doller',
                'currency_symbol' => '$',
                'currency_code' => 'USD',
                'currency_position' => 'left',
                'no_of_decimal' => 2,
                'thousand_separator' => ',',
                'decimal_separator' => '.',
                'is_primary' => 1,
                'created_by'=> 1,
                'updated_by'=>1
            ],
            [
                'currency_name' => 'Doller',
                'currency_symbol' => '$',
                'currency_code' => 'USD',
                'currency_position' => 'left',
                'no_of_decimal' => 2,
                'thousand_separator' => ',',
                'decimal_separator' => '.',
                'is_primary' => 1,
                'created_by'=> 2,
                'updated_by'=>2
            ],
            [
                'currency_name' => 'Doller',
                'currency_symbol' => '$',
                'currency_code' => 'USD',
                'currency_position' => 'left',
                'no_of_decimal' => 2,
                'thousand_separator' => ',',
                'decimal_separator' => '.',
                'is_primary' => 1,
                'created_by'=> 3,
                'updated_by'=>3
            ],
            [
                'currency_name' => 'Doller',
                'currency_symbol' => '$',
                'currency_code' => 'USD',
                'currency_position' => 'left',
                'no_of_decimal' => 2,
                'thousand_separator' => ',',
                'decimal_separator' => '.',
                'is_primary' => 1,
                'created_by'=> 14,
                'updated_by'=>14
            ],
            [
                'currency_name' => 'Doller',
                'currency_symbol' => '$',
                'currency_code' => 'USD',
                'currency_position' => 'left',
                'no_of_decimal' => 2,
                'thousand_separator' => ',',
                'decimal_separator' => '.',
                'is_primary' => 1,
                'created_by'=> 15,
                'updated_by'=>15
            ],
            [
                'currency_name' => 'Doller',
                'currency_symbol' => '$',
                'currency_code' => 'USD',
                'currency_position' => 'left',
                'no_of_decimal' => 2,
                'thousand_separator' => ',',
                'decimal_separator' => '.',
                'is_primary' => 1,
                'created_by'=> 16,
                'updated_by'=>16
            ],
            [
                'currency_name' => 'Doller',
                'currency_symbol' => '$',
                'currency_code' => 'USD',
                'currency_position' => 'left',
                'no_of_decimal' => 2,
                'thousand_separator' => ',',
                'decimal_separator' => '.',
                'is_primary' => 1,
                'created_by'=> 17,
                'updated_by'=>17
            ],
            [
                'currency_name' => 'Doller',
                'currency_symbol' => '$',
                'currency_code' => 'USD',
                'currency_position' => 'left',
                'no_of_decimal' => 2,
                'thousand_separator' => ',',
                'decimal_separator' => '.',
                'is_primary' => 1,
                'created_by'=> 18,
                'updated_by'=>18
            ],
            [
                'currency_name' => 'Doller',
                'currency_symbol' => '$',
                'currency_code' => 'USD',
                'currency_position' => 'left',
                'no_of_decimal' => 2,
                'thousand_separator' => ',',
                'decimal_separator' => '.',
                'is_primary' => 1,
                'created_by'=> 19,
                'updated_by'=>19
            ],
            [
                'currency_name' => 'Doller',
                'currency_symbol' => '$',
                'currency_code' => 'USD',
                'currency_position' => 'left',
                'no_of_decimal' => 2,
                'thousand_separator' => ',',
                'decimal_separator' => '.',
                'is_primary' => 1,
                'created_by'=> 20,
                'updated_by'=>20
            ],
            [
                'currency_name' => 'Doller',
                'currency_symbol' => '$',
                'currency_code' => 'USD',
                'currency_position' => 'left',
                'no_of_decimal' => 2,
                'thousand_separator' => ',',
                'decimal_separator' => '.',
                'is_primary' => 1,
                'created_by'=> 21,
                'updated_by'=>21
            ],
            [
                'currency_name' => 'Doller',
                'currency_symbol' => '$',
                'currency_code' => 'USD',
                'currency_position' => 'left',
                'no_of_decimal' => 2,
                'thousand_separator' => ',',
                'decimal_separator' => '.',
                'is_primary' => 1,
                'created_by'=> 22,
                'updated_by'=>22
            ],
            [
                'currency_name' => 'Doller',
                'currency_symbol' => '$',
                'currency_code' => 'USD',
                'currency_position' => 'left',
                'no_of_decimal' => 2,
                'thousand_separator' => ',',
                'decimal_separator' => '.',
                'is_primary' => 1,
                'created_by'=> 23,
                'updated_by'=>23
            ],
        ];

        foreach ($data as $key => $value) {
            Currency::create($value);
        }

        // Enable foreign key checks!
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
