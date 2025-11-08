<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BookingsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        // // Clear existing data
        // DB::table('booking_transactions')->delete();
        // DB::table('commission_earnings')->delete();
        // DB::table('booking_services')->delete();
        // DB::table('bookings')->delete();

        // // Insert into bookings table
        // DB::table('bookings')->insert([
        //     [
        //         'id' => 1,
        //         'note' => NULL,
        //         'status' => 'confirmed',
        //         'start_date_time' => '2025-02-19 17:30:00',
        //         'user_id' => 6,
        //         'branch_id' => 1,
        //         'created_by' => 2,
        //         'updated_by' => 2,
        //         'deleted_by' => NULL,
        //         'created_at' => '2025-02-19 11:56:24',
        //         'updated_at' => '2025-02-19 11:56:24',
        //         'deleted_at' => NULL,
        //     ],
        //     [
        //         'id' => 2,
        //         'note' => NULL,
        //         'status' => 'confirmed',
        //         'start_date_time' => '2025-02-27 09:15:00',
        //         'user_id' => 10,
        //         'branch_id' => 1,
        //         'created_by' => 2,
        //         'updated_by' => 2,
        //         'deleted_by' => NULL,
        //         'created_at' => '2025-02-19 11:56:48',
        //         'updated_at' => '2025-02-19 11:56:48',
        //         'deleted_at' => NULL,
        //     ],
        //     [
        //         'id' => 3,
        //         'note' => NULL,
        //         'status' => 'confirmed',
        //         'start_date_time' => '2025-03-05 09:15:00',
        //         'user_id' => 8,
        //         'branch_id' => 1,
        //         'created_by' => 2,
        //         'updated_by' => 2,
        //         'deleted_by' => NULL,
        //         'created_at' => '2025-02-19 11:57:10',
        //         'updated_at' => '2025-02-19 11:57:10',
        //         'deleted_at' => NULL,
        //     ],
        //     [
        //         'id' => 4,
        //         'note' => NULL,
        //         'status' => 'completed',
        //         'start_date_time' => '2025-03-04 09:30:00',
        //         'user_id' => 11,
        //         'branch_id' => 1,
        //         'created_by' => 2,
        //         'updated_by' => 2,
        //         'deleted_by' => NULL,
        //         'created_at' => '2025-02-19 11:57:46',
        //         'updated_at' => '2025-02-19 12:50:50',
        //         'deleted_at' => NULL,
        //     ],
        //     [
        //         'id' => 5,
        //         'note' => NULL,
        //         'status' => 'completed',
        //         'start_date_time' => '2025-02-20 10:15:00',
        //         'user_id' => 10,
        //         'branch_id' => 3,
        //         'created_by' => 2,
        //         'updated_by' => 2,
        //         'deleted_by' => NULL,
        //         'created_at' => '2025-02-19 11:58:17',
        //         'updated_at' => '2025-02-19 12:50:08',
        //         'deleted_at' => NULL,
        //     ]
        // ]);

        // // Insert into booking_services table
        // DB::table('booking_services')->insert([
        //     [
        //         'id' => 1,
        //         'sequance' => 0,
        //         'start_date_time' => '2025-02-19 17:30:00',
        //         'booking_id' => 1,
        //         'service_id' => 1,
        //         'employee_id' => 31,
        //         'service_price' => 50.0,
        //         'duration_min' => 60,
        //         'created_by' => NULL,
        //         'updated_by' => NULL,
        //         'deleted_by' => NULL,
        //         'created_at' => '2025-02-19 11:56:24',
        //         'updated_at' => '2025-02-19 11:56:24',
        //         'deleted_at' => NULL,
        //     ],
        //     [
        //         'id' => 2,
        //         'sequance' => 0,
        //         'start_date_time' => '2025-02-27 09:15:00',
        //         'booking_id' => 2,
        //         'service_id' => 3,
        //         'employee_id' => 31,
        //         'service_price' => 2000.0,
        //         'duration_min' => 55,
        //         'created_by' => NULL,
        //         'updated_by' => NULL,
        //         'deleted_by' => NULL,
        //         'created_at' => '2025-02-19 11:56:48',
        //         'updated_at' => '2025-02-19 11:56:48',
        //         'deleted_at' => NULL,
        //     ],
        //     [
        //         'id' => 3,
        //         'sequance' => 0,
        //         'start_date_time' => '2025-03-05 09:15:00',
        //         'booking_id' => 3,
        //         'service_id' => 4,
        //         'employee_id' => 35,
        //         'service_price' => 100.0,
        //         'duration_min' => 60,
        //         'created_by' => NULL,
        //         'updated_by' => NULL,
        //         'deleted_by' => NULL,
        //         'created_at' => '2025-02-19 11:57:10',
        //         'updated_at' => '2025-02-19 11:57:10',
        //         'deleted_at' => NULL,
        //     ],
        //     [
        //         'id' => 4,
        //         'sequance' => 1,
        //         'start_date_time' => '2025-03-05 10:15:00',
        //         'booking_id' => 3,
        //         'service_id' => 6,
        //         'employee_id' => 35,
        //         'service_price' => 200.0,
        //         'duration_min' => 50,
        //         'created_by' => NULL,
        //         'updated_by' => NULL,
        //         'deleted_by' => NULL,
        //         'created_at' => '2025-02-19 11:57:10',
        //         'updated_at' => '2025-02-19 11:57:10',
        //         'deleted_at' => NULL,
        //     ],
        //     [
        //         'id' => 5,
        //         'sequance' => 0,
        //         'start_date_time' => '2025-03-04 09:30:00',
        //         'booking_id' => 4,
        //         'service_id' => 4,
        //         'employee_id' => 35,
        //         'service_price' => 100.0,
        //         'duration_min' => 60,
        //         'created_by' => NULL,
        //         'updated_by' => NULL,
        //         'deleted_by' => NULL,
        //         'created_at' => '2025-02-19 11:57:46',
        //         'updated_at' => '2025-02-19 11:57:46',
        //         'deleted_at' => NULL,
        //     ],
        //     [
        //         'id' => 6,
        //         'sequance' => 1,
        //         'start_date_time' => '2025-03-04 10:30:00',
        //         'booking_id' => 4,
        //         'service_id' => 6,
        //         'employee_id' => 35,
        //         'service_price' => 200.0,
        //         'duration_min' => 50,
        //         'created_by' => NULL,
        //         'updated_by' => NULL,
        //         'deleted_by' => NULL,
        //         'created_at' => '2025-02-19 11:57:46',
        //         'updated_at' => '2025-02-19 11:57:46',
        //         'deleted_at' => NULL,
        //     ],
        //     [
        //         'id' => 7,
        //         'sequance' => 0,
        //         'start_date_time' => '2025-02-20 10:15:00',
        //         'booking_id' => 5,
        //         'service_id' => 3,
        //         'employee_id' => 33,
        //         'service_price' => 2000.0,
        //         'duration_min' => 55,
        //         'created_by' => NULL,
        //         'updated_by' => NULL,
        //         'deleted_by' => NULL,
        //         'created_at' => '2025-02-19 11:58:17',
        //         'updated_at' => '2025-02-19 11:58:17',
        //         'deleted_at' => NULL,
        //     ]
        // ]);

        // // Insert into commission_earnings table
        // DB::table('commission_earnings')->insert([
        //     [
        //         'id' => 1,
        //         'employee_id' => 33,
        //         'commissionable_type' => 'Modules\\Booking\\Models\\Booking',
        //         'commissionable_id' => 5,
        //         'commission_amount' => 200.0,
        //         'commission_status' => 'unpaid',
        //         'payment_date' => NULL,
        //         'created_by' => NULL,
        //         'updated_by' => NULL,
        //         'deleted_by' => NULL,
        //         'created_at' => '2025-02-19 12:50:08',
        //         'updated_at' => '2025-02-19 12:50:08',
        //         'deleted_at' => NULL,
        //     ],
        //     [
        //         'id' => 2,
        //         'employee_id' => 35,
        //         'commissionable_type' => 'Modules\\Booking\\Models\\Booking',
        //         'commissionable_id' => 4,
        //         'commission_amount' => 30.0,
        //         'commission_status' => 'unpaid',
        //         'payment_date' => NULL,
        //         'created_by' => NULL,
        //         'updated_by' => NULL,
        //         'deleted_by' => NULL,
        //         'created_at' => '2025-02-19 12:50:50',
        //         'updated_at' => '2025-02-19 12:50:50',
        //         'deleted_at' => NULL,
        //     ],
        // ]);

        // // Insert into booking_transactions table
        // DB::table('booking_transactions')->insert([
        //     [
        //         'id' => 1,
        //         'booking_id' => 5,
        //         'external_transaction_id' => '',
        //         'transaction_type' => 'cash',
        //         'discount_percentage' => 0.0,
        //         'discount_amount' => 0.0,
        //         'tip_amount' => 0.0,
        //         'tax_percentage' => '[{"name":"Service Tax","type":"fixed","percent":0,"tax_amount":22},{"name":"GST","type":"percent","percent":28,"tax_amount":0}]',
        //         'payment_status' => 1,
        //         'request_token' => NULL,
        //         'created_by' => NULL,
        //         'updated_by' => NULL,
        //         'deleted_by' => NULL,
        //         'created_at' => '2025-02-19 12:50:08',
        //         'updated_at' => '2025-02-19 12:50:08',
        //         'deleted_at' => NULL,
        //     ],
        //     [
        //         'id' => 2,
        //         'booking_id' => 4,
        //         'external_transaction_id' => '',
        //         'transaction_type' => 'cash',
        //         'discount_percentage' => 0.0,
        //         'discount_amount' => 0.0,
        //         'tip_amount' => 0.0,
        //         'tax_percentage' => '[{"name":"Service Tax","type":"fixed","percent":0,"tax_amount":22},{"name":"GST","type":"percent","percent":28,"tax_amount":0}]',
        //         'payment_status' => 1,
        //         'request_token' => NULL,
        //         'created_by' => NULL,
        //         'updated_by' => NULL,
        //         'deleted_by' => NULL,
        //         'created_at' => '2025-02-19 12:50:50',
        //         'updated_at' => '2025-02-19 12:50:50',
        //         'deleted_at' => NULL,
        //     ],
        // ]);

    }
}
