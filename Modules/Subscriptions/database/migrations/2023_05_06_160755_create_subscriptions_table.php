<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->integer('plan_id')->nullable();
            $table->integer('user_id')->nullable();
            $table->string('transaction_id')->nullable();
            $table->double('amount')->nullable();
            $table->double('discount_amount')->nullable();
            $table->double('tax_amount')->nullable();
            $table->double('total_amount')->nullable();
            
            $table->string('currency')->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->string('status')->nullable();
            $table->tinyInteger('payment_method')->default(2)->comment('1 => Offline, 2 => Online');
            $table->json('plan_details')->nullable();
            $table->string('gateway_type')->nullable();
            $table->json('gateway_response')->nullable();
            $table->boolean('is_active')->default(1)->comment('1 => Yes, 0 => No');
            $table->integer('max_appointment')->default(0);
            $table->integer('max_branch')->default(0);
            $table->integer('max_service')->default(0);
            $table->integer('max_staff')->default(0);
            $table->integer('max_customer')->default(0);
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscriptions');
    }
};
