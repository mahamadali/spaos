<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSettingsTableForUserFallback extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            // Ensure created_by column exists and is properly indexed
            if (!Schema::hasColumn('settings', 'created_by')) {
                $table->integer('created_by')->unsigned()->nullable()->after('type');
            }
            
            // Add index for better performance on user-based queries
            $table->index(['name', 'created_by'], 'settings_name_created_by_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropIndex('settings_name_created_by_index');
        });
    }
}

