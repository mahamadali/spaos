<?php

namespace Modules\Subscriptions\database\seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SubscriptionsTransactionsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        
        $subscriptions_transactions = [
            [
                'subscriptions_id' => 1,
                'user_id' => 19,
                'amount' => 215,
                'payment_type' => 'stripe',
                'payment_status' => 'paid',
                'transaction_id' => NULL,
                'tax_data' => NULL,
                'discount_data' => NULL,
                'other_transactions_details' => NULL,
                'created_by' => 2,
                'updated_by' => 2,
                'deleted_by' => NULL,
                'deleted_at' => NULL,
                'created_at' => '2025-01-18 00:00:00',
                'updated_at' => '2025-01-18 00:00:00',
            ],
            [
                'subscriptions_id' => 2,
                'user_id' => 19,
                'amount' => 120,
                'payment_type' => 'stripe',
                'payment_status' => 'paid',
                'transaction_id' => NULL,
                'tax_data' => NULL,
                'discount_data' => NULL,
                'other_transactions_details' => NULL,
                'created_by' => 2,
                'updated_by' => 2,
                'deleted_by' => NULL,
                'deleted_at' => NULL,
                'created_at' => '2025-01-20 00:00:00',
                'updated_at' => '2025-01-20 00:00:00',
            ],
            [
                'subscriptions_id' => 4,
                'user_id' => 21,
                'amount' => 120,
                'payment_type' => 'stripe',
                'payment_status' => 'paid',
                'transaction_id' => NULL,
                'tax_data' => NULL,
                'discount_data' => NULL,
                'other_transactions_details' => NULL,
                'created_by' => 2,
                'updated_by' => 2,
                'deleted_by' => NULL,
                'deleted_at' => NULL,
                'created_at' => '2025-01-13 00:00:00',
                'updated_at' => '2025-01-13 00:00:00',
            ],
            [
                'subscriptions_id' => 5,
                'user_id' => 18,
                'amount' => 157.5,
                'payment_type' => 'stripe',
                'payment_status' => 'paid',
                'transaction_id' => NULL,
                'tax_data' => NULL,
                'discount_data' => NULL,
                'other_transactions_details' => NULL,
                'created_by' => 2,
                'updated_by' => 2,
                'deleted_by' => NULL,
                'deleted_at' => NULL,
                'created_at' => '2025-01-10 00:00:00',
                'updated_at' => '2025-01-10 00:00:00',
            ],
            [
                'subscriptions_id' => 6,
                'user_id' => 15,
                'amount' => 215,
                'payment_type' => 'stripe',
                'payment_status' => 'paid',
                'transaction_id' => NULL,
                'tax_data' => NULL,
                'discount_data' => NULL,
                'other_transactions_details' => NULL,
                'created_by' => 2,
                'updated_by' => 2,
                'deleted_by' => NULL,
                'deleted_at' => NULL,
                'created_at' => '2025-01-08 00:00:00',
                'updated_at' => '2025-01-08 00:00:00',
            ],
            
            [
                'subscriptions_id' => 8,
                'user_id' => 14,
                'amount' => 120,
                'payment_type' => 'stripe',
                'payment_status' => 'paid',
                'transaction_id' => NULL,
                'tax_data' => NULL,
                'discount_data' => NULL,
                'other_transactions_details' => NULL,
                'created_by' => 2,
                'updated_by' => 2,
                'deleted_by' => NULL,
                'deleted_at' => NULL,
                'created_at' => '2025-01-04 00:00:00',
                'updated_at' => '2025-01-04 00:00:00',
            ],
            [
                'subscriptions_id' => 9,
                'user_id' => 3,
                'amount' => 515,
                'payment_type' => 'stripe',
                'payment_status' => 'paid',
                'transaction_id' => NULL,
                'tax_data' => NULL,
                'discount_data' => NULL,
                'other_transactions_details' => NULL,
                'created_by' => 2,
                'updated_by' => 2,
                'deleted_by' => NULL,
                'deleted_at' => NULL,
                'created_at' => '2025-01-02 00:00:00',
                'updated_at' => '2025-01-02 00:00:00',
            ],
            [
                'subscriptions_id' => 11,
                'user_id' => 14,
                'amount' => 120,
                'payment_type' => 'stripe',
                'payment_status' => 'paid',
                'transaction_id' => NULL,
                'tax_data' => NULL,
                'discount_data' => NULL,
                'other_transactions_details' => NULL,
                'created_by' => 2,
                'updated_by' => 2,
                'deleted_by' => NULL,
                'deleted_at' => NULL,
                'created_at' => '2024-11-01 00:00:00',
                'updated_at' => '2024-11-01 00:00:00',
            ],
            [
                'subscriptions_id' => 12,
                'user_id' => 2,
                'amount' => 515,
                'payment_type' => 'stripe',
                'payment_status' => 'paid',
                'transaction_id' => NULL,
                'tax_data' => NULL,
                'discount_data' => NULL,
                'other_transactions_details' => NULL,
                'created_by' => 2,
                'updated_by' => 2,
                'deleted_by' => NULL,
                'deleted_at' => NULL,
                'created_at' => '2025-01-02 00:00:00',
                'updated_at' => '2025-01-02 00:00:00',
            ],
        ];
        
        DB::table('subscriptions_transactions')->insert($subscriptions_transactions);
    }
}