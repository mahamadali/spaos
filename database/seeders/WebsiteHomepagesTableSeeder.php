<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WebsiteHomepagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'id' => 1,
                'website_setting_id' => 1,
                'key' => 'banner_title',
                'value' => json_encode('Transform Your Salon With Effortless Management'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'website_setting_id' => 1,
                'key' => 'banner_subtitle',
                'value' => json_encode('Elevate your beauty and wellness journey with the exceptional range of experiences available on the Frezka booking system'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'website_setting_id' => 1,
                'key' => 'banner_badge_text',
                'value' => json_encode('Scalable pricing with a range of features from essential to advanced tools.'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'website_setting_id' => 1,
                'key' => 'banner_link',
                'value' => json_encode('https://youtu.be/8-E1LbChJ88?si=caSTfjvm7PwSgHWa'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'website_setting_id' => 1,
                'key' => 'about_title',
                'value' => json_encode('Why Frezka'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'website_setting_id' => 1,
                'key' => 'about_subtitle',
                'value' => json_encode('Quick Go Through About Frezka'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 7,
                'website_setting_id' => 1,
                'key' => 'about_description',
                'value' => json_encode('We save your time, drive growth, and keep clients coming back. Here’s how our platform boosts salon business success.'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 8,
                'website_setting_id' => 1,
                'key' => 'video',
                'value' => json_encode(null),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 9,
                'website_setting_id' => 1,
                'key' => 'video_type',
                'value' => json_encode('youtube'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 10,
                'website_setting_id' => 1,
                'key' => 'video_url',
                'value' => json_encode('https://youtu.be/urPq7Qq0lXk?si=phGkmrCqciVBx9ro'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 11,
                'website_setting_id' => 1,
                'key' => 'chooseUs_title',
                'value' => json_encode('Why Frezka'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 12,
                'website_setting_id' => 1,
                'key' => 'chooseUs_subtitle',
                'value' => json_encode('Why Choose frezkaaa'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 13,
                'website_setting_id' => 1,
                'key' => 'choose_us_feature_list',
               'value' => json_encode([
                                     ['id' => '1', 'title' => 'aaa'],
                                     ['id' => '2', 'title' => 'aaa'],
                                     ['id' => '3', 'title' => 'aaa']
                                 ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 14,
                'website_setting_id' => 1,
                'key' => 'chooseUs_description',
                'value' => json_encode('With an intuitive booking system, expert selection, & exclusive offers, our all-in-one platform ensures seamless operations while enhancing customer loyalty.'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 15,
                'website_setting_id' => 1,
                'key' => 'banner_image1',
                'value' => json_encode('website_homepage/banner_image1_677ce98412e36.jpg'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 16,
                'website_setting_id' => 1,
                'key' => 'banner_image2',
                'value' => json_encode('website_homepage/banner_image2_677ce98415100.png'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 17,
                'website_setting_id' => 1,
                'key' => 'banner_image3',
                'value' => json_encode('website_homepage/banner_image3_677ce9a1db3a7.jpg'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 18,
                'website_setting_id' => 1,
                'key' => 'video_img',
                'value' => json_encode('website_homepage/video_img_677cea61b22a7.png'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 19,
                'website_setting_id' => 1,
                'key' => 'chooseUs_image',
                'value' => json_encode('website_homepage/chooseUs_image_677cea61b41d3.jpg'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('website_homepages')->insert($data);
    }
}
