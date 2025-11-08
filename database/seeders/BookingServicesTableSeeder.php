<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class BookingServicesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('booking_services')->delete();
        
        \DB::table('booking_services')->insert(array (
            0 => 
            array (
                'id' => 1,
                'sequance' => 0,
                'start_date_time' => '2025-02-19 17:30:00',
                'booking_id' => 1,
                'service_id' => 1,
                'employee_id' => 31,
                'service_price' => 50.0,
                'duration_min' => 60,
                'created_by' => NULL,
                'updated_by' => NULL,
                'deleted_by' => NULL,
                'created_at' => '2025-02-19 11:56:24',
                'updated_at' => '2025-02-19 11:56:24',
                'deleted_at' => NULL,
            ),
            1 => 
            array (
                'id' => 2,
                'sequance' => 0,
                'start_date_time' => '2025-02-27 09:15:00',
                'booking_id' => 2,
                'service_id' => 3,
                'employee_id' => 31,
                'service_price' => 2000.0,
                'duration_min' => 55,
                'created_by' => NULL,
                'updated_by' => NULL,
                'deleted_by' => NULL,
                'created_at' => '2025-02-19 11:56:48',
                'updated_at' => '2025-02-19 11:56:48',
                'deleted_at' => NULL,
            ),
            2 => 
            array (
                'id' => 3,
                'sequance' => 0,
                'start_date_time' => '2025-03-05 09:15:00',
                'booking_id' => 3,
                'service_id' => 4,
                'employee_id' => 35,
                'service_price' => 100.0,
                'duration_min' => 60,
                'created_by' => NULL,
                'updated_by' => NULL,
                'deleted_by' => NULL,
                'created_at' => '2025-02-19 11:57:10',
                'updated_at' => '2025-02-19 11:57:10',
                'deleted_at' => NULL,
            ),
            3 => 
            array (
                'id' => 4,
                'sequance' => 1,
                'start_date_time' => '2025-03-05 10:15:00',
                'booking_id' => 3,
                'service_id' => 6,
                'employee_id' => 35,
                'service_price' => 200.0,
                'duration_min' => 50,
                'created_by' => NULL,
                'updated_by' => NULL,
                'deleted_by' => NULL,
                'created_at' => '2025-02-19 11:57:10',
                'updated_at' => '2025-02-19 11:57:10',
                'deleted_at' => NULL,
            ),
            4 => 
            array (
                'id' => 5,
                'sequance' => 0,
                'start_date_time' => '2025-03-04 09:30:00',
                'booking_id' => 4,
                'service_id' => 4,
                'employee_id' => 35,
                'service_price' => 100.0,
                'duration_min' => 60,
                'created_by' => NULL,
                'updated_by' => NULL,
                'deleted_by' => NULL,
                'created_at' => '2025-02-19 11:57:46',
                'updated_at' => '2025-02-19 11:57:46',
                'deleted_at' => NULL,
            ),
            5 => 
            array (
                'id' => 6,
                'sequance' => 1,
                'start_date_time' => '2025-03-04 10:30:00',
                'booking_id' => 4,
                'service_id' => 6,
                'employee_id' => 35,
                'service_price' => 200.0,
                'duration_min' => 50,
                'created_by' => NULL,
                'updated_by' => NULL,
                'deleted_by' => NULL,
                'created_at' => '2025-02-19 11:57:46',
                'updated_at' => '2025-02-19 11:57:46',
                'deleted_at' => NULL,
            ),
            6 => 
            array (
                'id' => 7,
                'sequance' => 0,
                'start_date_time' => '2025-02-20 10:15:00',
                'booking_id' => 5,
                'service_id' => 3,
                'employee_id' => 33,
                'service_price' => 2000.0,
                'duration_min' => 55,
                'created_by' => NULL,
                'updated_by' => NULL,
                'deleted_by' => NULL,
                'created_at' => '2025-02-19 11:58:17',
                'updated_at' => '2025-02-19 11:58:17',
                'deleted_at' => NULL,
            ),
        ));
        
        
    }
}