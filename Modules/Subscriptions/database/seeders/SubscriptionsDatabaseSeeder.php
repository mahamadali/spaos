<?php

namespace Modules\Subscriptions\database\seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use Modules\Subscriptions\Models\Plan;

class SubscriptionsDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $subscriptions = [
            [
                'plan_id' => 4, // Elite
                'user_id' => 19, // Jessica
                'transaction_id' => 'txn_19_jessica',
                'amount' => 200,
                'discount_amount' => 0,
                'tax_amount' => 15,
                'total_amount' => 215,
                'currency' => 'USD',
                'start_date' => Carbon::parse('2025-01-18'),
                'end_date' => Carbon::parse('2026-01-18'),
                'status' => 'active' ,
                'payment_method' => 2, // Online
                'gateway_type' => 'stripe',
                'gateway_response' => json_encode(['status' => 'success']),
                'is_active' => 1,
                'max_appointment' => 100,
                'max_branch' => 10,
                'max_service' => 50,
                'max_staff' => 20,
                'max_customer' => 50,
                'created_by' => 1,
                'updated_by' => 1,
                'deleted_by' => null,
                'created_at' => Carbon::parse('2025-01-18'),
                'updated_at' => Carbon::parse('2025-01-18'),
                'deleted_at' => null,
            ],
            [
                'plan_id' => 3, // Elite
                'user_id' => 19, // Jessica
                'transaction_id' => 'txn_19_jessica',
                'amount' => 100,
                'discount_amount' => 0,
                'tax_amount' => 20,
                'total_amount' => 120,
                'currency' => 'USD',
                'start_date' => Carbon::parse('2025-01-20'),
                'end_date' => Carbon::parse('2026-03-20'),
                'status' => 'Inactive' ,
                'payment_method' => 2, // Online
                'gateway_type' => 'stripe',
                'gateway_response' => json_encode(['status' => 'success']),
                'is_active' => 1,
                'max_appointment' => 10,
                'max_branch' => 2,
                'max_service' => 10,
                'max_staff' => 5,
                'max_customer' => 5,
                'created_by' => 1,
                'updated_by' => 1,
                'deleted_by' => null,
                'created_at' =>  Carbon::parse('2025-01-20'),
                'updated_at' =>  Carbon::parse('2025-01-20'),
                'deleted_at' => null,
            ],
            [
                'plan_id' => 6, // Standard
                'user_id' => 20, // Jacob
                'transaction_id' => 'txn_20_jacob',
                'amount' => 150,
                'discount_amount' => 0,
                'tax_amount' => 7.5,
                'total_amount' => 157.5,
                'currency' => 'USD',
                'start_date' => Carbon::parse('2025-02-15'),
                'end_date' => Carbon::parse('2025-05-15'),
                'status' => 'active' ,
                'payment_method' => 1, // Offline
                'gateway_type' => 'manual',
                'gateway_response' => json_encode(['status' => 'success']),
                'is_active' => 1,
                'max_appointment' => 20,
                'max_branch' => 5,
                'max_service' => 25,
                'max_staff' => 10,
                'max_customer' => 15,
                'created_by' => 1,
                'updated_by' => 1,
                'deleted_by' => null,
                'created_at' =>  Carbon::parse('2025-01-15'),
                'updated_at' =>  Carbon::parse('2025-01-15'),
                'deleted_at' => null,
            ],
            [
                'plan_id' => 3, // Premium
                'user_id' => 21, // Ashley
                'transaction_id' => 'txn_21_ashley',
                'amount' => 100,
                'discount_amount' => 0,
                'tax_amount' => 20,
                'total_amount' => 120,
                'currency' => 'USD',
                'start_date' => Carbon::parse('2025-03-13'),
                'end_date' => Carbon::parse('2025-06-13'),
                'status' => 'active' ,
                'payment_method' => 2, // Online
                'gateway_type' => 'stripe',
                'gateway_response' => json_encode(['status' => 'success']),
                'is_active' => 1,
                'max_appointment' => 10,
                'max_branch' => 2,
                'max_service' => 10,
                'max_staff' => 5,
                'max_customer' => 5,
                'created_by' => 1,
                'updated_by' => 1,
                'deleted_by' => null,
                'created_at' => Carbon::parse('2025-01-13'),
                'updated_at' => Carbon::parse('2025-01-13'),
                'deleted_at' => null,
            ],
            [
                'plan_id' => 6, // Standard
                'user_id' => 18, // Michael
                'transaction_id' => 'txn_18_michael',
                'amount' => 150,
                'discount_amount' => 0,
                'tax_amount' => 7.5,
                'total_amount' => 157.5,
                'currency' => 'USD',
                'start_date' => Carbon::parse('2025-03-10'),
                'end_date' => Carbon::parse('2025-07-10'),
                'status' => 'active' ,
                'payment_method' => 2, // Online
                'gateway_type' => 'stripe',
                'gateway_response' => json_encode(['status' => 'success']),
                'is_active' => 1,
                'max_appointment' => 20,
                'max_branch' => 5,
                'max_service' => 25,
                'max_staff' => 10,
                'max_customer' => 15,
                'created_by' => 1,
                'updated_by' => 1,
                'deleted_by' => null,
                'created_at' => Carbon::parse('2025-01-10'),
                'updated_at' => Carbon::parse('2025-01-10'),
                'deleted_at' => null,
            ],
            [
                'plan_id' => 4, // Elite
                'user_id' => 15, // Alex
                'transaction_id' => 'txn_15_alex',
                'amount' => 200,
                'discount_amount' => 0,
                'tax_amount' => 15,
                'total_amount' => 215,
                'currency' => 'USD',
                'start_date' => Carbon::parse('2025-01-08'),
                'end_date' => Carbon::parse('2026-01-08'),
                'status' => 'active' ,
                'payment_method' => 2, // Online
                'gateway_type' => 'stripe',
                'gateway_response' => json_encode(['status' => 'success']),
                'is_active' => 1,
                'max_appointment' => 100,
                'max_branch' => 10,
                'max_service' => 50,
                'max_staff' => 20,
                'max_customer' => 50,
                'created_by' => 1,
                'updated_by' => 1,
                'deleted_by' => null,
                'created_at' =>  Carbon::parse('2025-01-08'),
                'updated_at' =>  Carbon::parse('2025-01-08'),
                'deleted_at' => null,
            ],
            [
                'plan_id' => 2, // Basic
                'user_id' => 16, // Emily
                'transaction_id' => 'txn_16_emily',
                'amount' => 50,
                'discount_amount' => 0,
                'tax_amount' => 2.5,
                'total_amount' => 52.5,
                'currency' => 'USD',
                'start_date' => Carbon::parse('2025-04-15'),
                'end_date' => Carbon::parse('2025-05-15'),
                'status' => 'active' ,
                'payment_method' => 1, // Offline
                'gateway_type' => 'manual',
                'gateway_response' => json_encode(['status' => 'success']),
                'is_active' => 1,
                'max_appointment' => 5,
                'max_branch' => 1,
                'max_service' => 2,
                'max_staff' => 2,
                'max_customer' => 2,
                'created_by' => 1,
                'updated_by' => 1,
                'deleted_by' => null,
                'created_at' => Carbon::parse('2025-01-08'),
                'updated_at' => Carbon::parse('2025-01-08'),
                'deleted_at' => null,
            ],
            [
                'plan_id' => 3, // Premium
                'user_id' => 14, // Jane
                'transaction_id' => 'txn_14_jane',
                'amount' => 100,
                'discount_amount' => 0,
                'tax_amount' => 20,
                'total_amount' => 120,
                'currency' => 'USD',
                'start_date' => Carbon::parse('2025-03-04'),
                'end_date' => Carbon::parse('2025-06-04'),
                'status' => 'active' ,
                'payment_method' => 2, // Online
                'gateway_type' => 'stripe',
                'gateway_response' => json_encode(['status' => 'success']),
                'is_active' => 1,
                'max_appointment' => 10,
                'max_branch' => 2,
                'max_service' => 10,
                'max_staff' => 5,
                'max_customer' => 5,
                'created_by' => 1,
                'updated_by' => 1,
                'deleted_by' => null,
                'created_at' => Carbon::parse('2025-01-04'),
                'updated_at' => Carbon::parse('2025-01-04'),
                'deleted_at' => null,
            ],
            [
                'plan_id' => 5, // Super Elite
                'user_id' => 3, // John
                'transaction_id' => 'txn_03_john',
                'amount' => 500,
                'discount_amount' => 0,
                'tax_amount' => 15,
                'total_amount' => 515,
                'currency' => 'USD',
                'start_date' => Carbon::parse('2025-01-02'),
                'end_date' => Carbon::parse('2026-01-02'),
                'status' => 'active' ,
                'payment_method' => 2, // Online
                'gateway_type' => 'stripe',
                'gateway_response' => json_encode(['status' => 'success']),
                'is_active' => 1,
                'max_appointment' => 200,
                'max_branch' => 20,
                'max_service' => 70,
                'max_staff' => 50,
                'max_customer' => 150,
                'created_by' => 1,
                'updated_by' => 1,
                'deleted_by' => null,
                'created_at' => Carbon::parse('2025-01-02'),
                'updated_at' => Carbon::parse('2025-01-02'),
                'deleted_at' => null,
            ],
            [
                'plan_id' => 2, // Basic
                'user_id' => 18, // Michael
                'transaction_id' => 'txn_18_michael',
                'amount' => 50,
                'discount_amount' => 0,
                'tax_amount' => 2.5,
                'total_amount' => 52.5,
                'currency' => 'USD',
                'start_date' => Carbon::parse('2025-04-07'),
                'end_date' => Carbon::parse('2025-07-07'),
                'status' => 'Inactive',
                'payment_method' => 1, // Offline
                'gateway_type' => 'manual',
                'gateway_response' => json_encode(['status' => 'success']),
                'is_active' => 0,
                'max_appointment' => 5,
                'max_branch' => 1,
                'max_service' => 2,
                'max_staff' => 2,
                'max_customer' => 2,
                'created_by' => 1,
                'updated_by' => 1,
                'deleted_by' => null,
                'created_at' => Carbon::parse('2024-12-07'),
                'updated_at' => Carbon::parse('2024-12-07'),
                'deleted_at' => null,
            ],
            [
                'plan_id' => 3, // Premium
                'user_id' => 14, // Jane
                'transaction_id' => 'txn_14_jane',
                'amount' => 100,
                'discount_amount' => 0,
                'tax_amount' => 20,
                'total_amount' => 120,
                'currency' => 'USD',
                'start_date' => Carbon::parse('2024-11-01'),
                'end_date' => Carbon::parse('2025-01-01'),
                'status' => 'Inactive',
                'payment_method' => 2, // Online
                'gateway_type' => 'stripe',
                'gateway_response' => json_encode(['status' => 'success']),
                'is_active' => 0,
                'max_appointment' => 10,
                'max_branch' => 2,
                'max_service' => 10,
                'max_staff' => 5,
                'max_customer' => 5,
                'created_by' => 1,
                'updated_by' => 1,
                'deleted_by' => null,
                'created_at' => Carbon::parse('2024-11-01'),
                'updated_at' => Carbon::parse('2024-11-01'),
                'deleted_at' => null,
            ],
            [
                'plan_id' => 5, // Super Elite
                'user_id' => 2, // demo_admin
                'transaction_id' => 'txn_03_admin',
                'amount' => 500,
                'discount_amount' => 0,
                'tax_amount' => 15,
                'total_amount' => 515,
                'currency' => 'USD',
                'start_date' => Carbon::parse('2025-01-02'),
                'end_date' => Carbon::parse('2026-01-02'),
                'status' => 'active' ,
                'payment_method' => 2, // Online
                'gateway_type' => 'stripe',
                'gateway_response' => json_encode(['status' => 'success']),
                'is_active' => 1,
                'max_appointment' => 200,
                'max_branch' => 20,
                'max_service' => 70,
                'max_staff' => 50,
                'max_customer' => 150,
                'created_by' => 1,
                'updated_by' => 1,
                'deleted_by' => null,
                'created_at' => Carbon::parse('2025-01-02'),
                'updated_at' => Carbon::parse('2025-01-02'),
                'deleted_at' => null,
            ],
        ];

        DB::table('subscriptions')->insert($subscriptions);

        $insertedSubscriptions = DB::table('subscriptions')->get();

        foreach ($insertedSubscriptions as $subscription) {
            $planDetail = Plan::where('id', $subscription->plan_id)->first();

            if ($planDetail) {
                DB::table('subscriptions')
                    ->where('id', $subscription->id)
                    ->update(['plan_details' => json_encode($planDetail)]);
            }
        }

        $activeSubscriptions = collect($subscriptions)
            ->where('status', 'active');

        // Get all users who have at least one active subscription
        $activeUserIds = collect($subscriptions)
            ->where('status', 'active') // Filter active subscriptions
            ->pluck('user_id') // Get user IDs
            ->unique() // Ensure unique user IDs
            ->toArray();

        // Update `is_subscribe` for active users
        DB::table('users')
            ->whereIn('id', $activeUserIds)
            ->update(['is_subscribe' => 1]);

        foreach ($activeSubscriptions as $subscription) {
            $user = User::find($subscription['user_id']); // Get the user
            $plan = Plan::find($subscription['plan_id']); // Get the plan
        
            if ($user && $plan) {
                $plan->givePermissionToUser($user->id); // Assign plan permission to user
            }
        }
    }
}
