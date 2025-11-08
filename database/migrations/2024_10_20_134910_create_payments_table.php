<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('plan_id');
            $table->decimal('amount',8,2);
            $table->string('currency')->default('inr');
            $table->integer('subscription_id')->nullable();
            $table->tinyInteger('payment_method')->default(1)->comment('1 => Offline, 2 => Online');
            $table->timestamp('payment_date');
            $table->json('plan_details')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0 => Pending, 1 => Approved, 2 => Rejected');
            $table->text('image')->nullable();
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
