<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WebsiteSettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('website_settings')->insert([
            'website_title' => 'aaa',
            'facebook_link' => 'https://www.facebook.com/iqonicdesign/',
            'instagram_link' => 'https://www.instagram.com/iqonicdesign/?igshid=YmMyMTA2M2Y%3D',
            'youtube_link' => 'https://www.youtube.com/iqonicdesign',
            'twitter_link' => 'https://twitter.com/iqonicdesign',
            'website_logo' => 'website_logo/website_logo_677cec1e0bc32.jpeg',
            'about_us' => '<h3 class="section-title mb-0">Empowering Salons to Streamline Bookings & Client Experiences</h3>
                            <p class="title-description mb-2">Welcome to Frezka, your all-in-one solution for transforming how salon and spa businesses operate in the modern world. Designed with innovation and efficiency in mind, Frezka empowers you to manage your business effortlessly, delivering a seamless experience for both owners and customers.
                                We understand the challenges salon and spa businesses face—whether it’s managing bookings, optimizing staff schedules, or enhancing customer satisfaction. That’s why Frezka was developed as a powerful Flutter-based platform to simplify operations, save time, and grow your business.</p>',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
