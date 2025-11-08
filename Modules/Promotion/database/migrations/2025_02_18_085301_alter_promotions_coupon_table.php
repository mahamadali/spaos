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
        Schema::table('promotions_coupon', function (Blueprint $table) {
            $table->integer('use_limit')->nullable()->default(null)->change();
        });    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promotions_coupon', function (Blueprint $table) {
            $table->integer('use_limit')->default(1)->change();
        });
    }
};
