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
        Schema::create('homepage_settings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('banner_image1')->nullable();
            $table->string('banner_image2')->nullable();
            $table->string('banner_image3')->nullable();
            $table->string('banner_title')->nullable();
            $table->string('banner_subtitle')->nullable();
            $table->string('banner_badge_text')->nullable();
            $table->string('banner_link')->nullable();
            $table->string('about_title')->nullable();
            $table->string('about_subtitle')->nullable();
            $table->text('about_description')->nullable();
            $table->string('video')->nullable();
            $table->string('video_type')->nullable();
            $table->string('video_url')->nullable();
            $table->string('chooseUs_image')->nullable();
            $table->string('chooseUs_title')->nullable();
            $table->string('chooseUs_subtitle')->nullable();
            $table->json('choose_us_feature_list')->nullable(); 
            $table->text('chooseUs_description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homepage_settings');
    }
};
