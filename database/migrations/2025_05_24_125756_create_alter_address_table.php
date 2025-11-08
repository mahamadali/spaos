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
        Schema::table('addresses', function (Blueprint $table) {
            // Add new columns
            // $table->string('first_name')->nullable();
            // $table->string('last_name')->nullable();


            // Or modify existing columns
            // $table->string('name')->nullable()->change();
        });
    }
};
