<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            // Add user tracking columns
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            // Add soft delete column
            $table->softDeletes();
        });

        // Update existing blog records with super_admin user ID
        // $superAdminUser = \App\Models\User::role('super admin')->first();

        // if ($superAdminUser) {
        \DB::table('blogs')
            ->whereNull('created_by')
            ->update([
                'created_by' => 1,
                'updated_by' => 1,
                'updated_at' => now()
            ]);
        // }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
            $table->dropSoftDeletes();
        });
    }
};
