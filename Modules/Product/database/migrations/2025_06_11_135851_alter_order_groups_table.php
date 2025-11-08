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
        Schema::table('order_groups', function (Blueprint $table) {
            $table->json('tax')->nullable()->after('sub_total_amount'); // or after any column you want
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_groups', function (Blueprint $table) {
            $table->dropColumn('tax');
        });
    }
};
