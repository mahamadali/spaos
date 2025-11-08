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
        Schema::table('plan', function (Blueprint $table) {
            $table->boolean('has_discount')->default(false);
            $table->decimal('discount_value', 8, 2)->nullable();
            $table->string('discount_type', 20)->nullable();
            $table->decimal('discounted_price', 8, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plan', function (Blueprint $table) {
            $table->dropColumn([
                'has_discount',
                'discount_value',
                'discount_type',
                'discounted_price'
            ]);
        });
    }
};
