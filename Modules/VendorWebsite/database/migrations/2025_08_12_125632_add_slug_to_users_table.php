<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('slug')->unique()->after('username')->nullable();
        });
        User::chunkById(100, function ($users) {
            foreach ($users as $user) {
                // Generate new slug from first and last name
                $newSlug = $user->generateSlug();

                // Update user with new slug while preserving old slug
                $user->updateSlug($newSlug);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['slug', 'old_slug']);
        });
    }
};
