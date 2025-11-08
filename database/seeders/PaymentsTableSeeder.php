<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Modules\Subscriptions\Models\Plan;

class PaymentsTableSeeder extends Seeder
{
    public function run()
    {
        $payments = [
            [
                'user_id' => 16, // Emily Davis
                'plan_id' => 2, // Basic
                'amount' => 52.5,
                'currency' => 'inr',
                'subscription_id' => null,
                'payment_method' => 1, // Offline
                'payment_date' => Carbon::parse('2025-01-17'),
                'status' => 1, // Approved
                'image' => null,
                'created_by' => 1,
                'updated_by' => 1,
                'deleted_by' => null,
                'created_at' => Carbon::parse('2025-01-17'),
                'updated_at' => Carbon::parse('2025-01-17'),
            ],
            [
                'user_id' => 17, // Chris Brown
                'plan_id' => 3, // Premium
                'amount' => 120,
                'currency' => 'inr',
                'subscription_id' => null,
                'payment_method' => 1, // Offline
                'payment_date' => Carbon::parse('2025-01-14'),
                'status' => 0, // Pending
                'image' => null,
                'created_by' => 1,
                'updated_by' => 1,
                'deleted_by' => null,
                'created_at' => Carbon::parse('2025-01-14'),
                'updated_at' => Carbon::parse('2025-01-14'),
            ],
            [
                'user_id' => 20, // Jacob Miller
                'plan_id' => 6, // Standard
                'amount' => 157.5,
                'currency' => 'inr',
                'subscription_id' => null,
                'payment_method' => 1, // Offline
                'payment_date' => Carbon::parse('2025-01-15'),
                'status' => 1, // Approved
                'image' => null,
                'created_by' => 1,
                'updated_by' => 1,
                'deleted_by' => null,
                'created_at' => Carbon::parse('2025-01-15'),
                'updated_at' => Carbon::parse('2025-01-15'),
            ],
            [
                'user_id' => 15, // Alex Johnson
                'plan_id' => 3, // Premium
                'amount' => 120,
                'currency' => 'inr',
                'subscription_id' => null,
                'payment_method' => 1, // Offline
                'payment_date' => Carbon::parse('2025-01-14'),
                'status' => 0, // Pending
                'image' => null,
                'created_by' => 1,
                'updated_by' => 1,
                'deleted_by' => null,
                'created_at' => Carbon::parse('2025-01-14'),
                'updated_at' => Carbon::parse('2025-01-14'),
            ],
            [
                'user_id' => 21, // Ashley Davis
                'plan_id' => 6, // Standard
                'amount' => 157.5,
                'currency' => 'inr',
                'subscription_id' => null,
                'payment_method' => 1, // Offline
                'payment_date' => Carbon::parse('2025-01-17'),
                'status' => 0, // Pending
                'image' => null,
                'created_by' => 1,
                'updated_by' => 1,
                'deleted_by' => null,
                'created_at' => Carbon::parse('2025-01-17'),
                'updated_at' => Carbon::parse('2025-01-17'),
            ],
            [
                'user_id' => 18,
                'plan_id' => 2, // Based on subscriptions_id
                'amount' => 52.5,
                'currency' => 'inr',
                'subscription_id' => 10,
                'payment_method' => 1, // Offline
                'payment_date' => Carbon::parse('2024-12-07'),
                'status' => 1, // Approved
                'image' => null,
                'created_by' => 1,
                'updated_by' => 1,
                'deleted_by' => null,
                'created_at' => Carbon::parse('2024-12-07'),
                'updated_at' => Carbon::parse('2024-12-07'),
            ],
            [
                'user_id' => 19,
                'plan_id' => 4, // Based on subscriptions_id
                'amount' => 215,
                'currency' => 'inr',
                'subscription_id' => 1,
                'payment_method' => 2, // Online
                'payment_date' => Carbon::parse('2025-01-18'),
                'status' => 1, // Approved
                'image' => null,
                'created_by' => 2,
                'updated_by' => 2,
                'deleted_by' => null,
                'created_at' => Carbon::parse('2025-01-18'),
                'updated_at' => Carbon::parse('2025-01-18'),
            ],
            [
                'user_id' => 21,
                'plan_id' => 3,
                'amount' => 120,
                'currency' => 'inr',
                'subscription_id' => 4,
                'payment_method' => 2,
                'payment_date' => Carbon::parse('2025-01-13'),
                'status' => 1,
                'image' => null,
                'created_by' => 2,
                'updated_by' => 2,
                'deleted_by' => null,
                'created_at' => Carbon::parse('2025-01-13'),
                'updated_at' => Carbon::parse('2025-01-13'),
            ],
            [
                'user_id' => 18,
                'plan_id' => 6,
                'amount' => 157.5,
                'currency' => 'inr',
                'subscription_id' => 5,
                'payment_method' => 2,
                'payment_date' => Carbon::parse('2025-01-10'),
                'status' => 1,
                'image' => null,
                'created_by' => 2,
                'updated_by' => 2,
                'deleted_by' => null,
                'created_at' => Carbon::parse('2025-01-10'),
                'updated_at' => Carbon::parse('2025-01-10'),
            ],
            [
                'user_id' => 15,
                'plan_id' => 4,
                'amount' => 215,
                'currency' => 'inr',
                'subscription_id' => 6,
                'payment_method' => 2,
                'payment_date' => Carbon::parse('2025-01-08'),
                'status' => 1,
                'image' => null,
                'created_by' => 2,
                'updated_by' => 2,
                'deleted_by' => null,
                'created_at' => Carbon::parse('2025-01-08'),
                'updated_at' => Carbon::parse('2025-01-08'),
            ],
            [
                'user_id' => 14,
                'plan_id' => 3,
                'amount' => 120,
                'currency' => 'inr',
                'subscription_id' => 8,
                'payment_method' => 2,
                'payment_date' => Carbon::parse('2025-01-04'),
                'status' => 1,
                'image' => null,
                'created_by' => 2,
                'updated_by' => 2,
                'deleted_by' => null,
                'created_at' => Carbon::parse('2025-01-04'),
                'updated_at' => Carbon::parse('2025-01-04'),
            ],
            [
                'user_id' => 3,
                'plan_id' => 5,
                'amount' => 515,
                'currency' => 'inr',
                'subscription_id' => 9,
                'payment_method' => 2,
                'payment_date' => Carbon::parse('2025-01-02'),
                'status' => 1,
                'image' => null,
                'created_by' => 2,
                'updated_by' => 2,
                'deleted_by' => null,
                'created_at' => Carbon::parse('2025-01-02'),
                'updated_at' => Carbon::parse('2025-01-02'),
            ],
            [
                'user_id' => 2,
                'plan_id' => 5,
                'amount' => 515,
                'currency' => 'inr',
                'subscription_id' => 12,
                'payment_method' => 2,
                'payment_date' => Carbon::parse('2025-01-02'),
                'status' => 1,
                'image' => null,
                'created_by' => 2,
                'updated_by' => 2,
                'deleted_by' => null,
                'created_at' => Carbon::parse('2025-01-02'),
                'updated_at' => Carbon::parse('2025-01-02'),
            ], //
            [
                'user_id' => 19,
                'plan_id' => 3,
                'amount' => 120,
                'currency' => 'inr',
                'subscription_id' => 2,
                'payment_method' => 2,
                'payment_date' => Carbon::parse('2025-01-20'),
                'status' => 1,
                'image' => null,
                'created_by' => 2,
                'updated_by' => 2,
                'deleted_by' => null,
                'created_at' => Carbon::parse('2025-01-20'),
                'updated_at' => Carbon::parse('2025-01-20'),
            ], 
            [
                'user_id' => 14,
                'plan_id' => 3,
                'amount' => 120,
                'currency' => 'inr',
                'subscription_id' => 11,
                'payment_method' => 2,
                'payment_date' => Carbon::parse('2024-11-01'),
                'status' => 1,
                'image' => null,
                'created_by' => 2,
                'updated_by' => 2,
                'deleted_by' => null,
                'created_at' => Carbon::parse('2024-11-01'),
                'updated_at' => Carbon::parse('2024-11-01'),
            ], 
        ];

        DB::table('payments')->insert($payments);

        $insertedPayments = DB::table('payments')->get();

        foreach ($insertedPayments as $payment) {
            $planDetail = Plan::where('id', $payment->plan_id)->first();

            if ($planDetail) {
                DB::table('payments')
                    ->where('id', $payment->id)
                    ->update(['plan_details' => json_encode($planDetail)]);
            }
        }

    }
}
