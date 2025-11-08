<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WebsiteFeaturesTableSeeder extends Seeder
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
                'website_setting_id' => 1,
                'title' => 'Tiered Subscription Plans',
                'description' => 'Create flexible plans with multiple tiers to suit different customer needs.',
                'image' => '/dummy-images/features/img1.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'website_setting_id' => 1,
                'title' => 'Appointment Booking',
                'description' => 'Effortlessly schedule and manage client appointments with automated reminders.',
                'image' => '/dummy-images/features/img2.jpeg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'website_setting_id' => 1,
                'title' => 'Analytics and Reporting',
                'description' => 'Gain insights into revenue, bookings, and client trends with detailed reports.',
                'image' => '/dummy-images/features/img3.jpeg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'website_setting_id' => 1,
                'title' => 'Staff Scheduling',
                'description' => 'Manage staff shifts, availability, and work hours with a simple calendar view.',
                'image' => '/dummy-images/features/img4.jpeg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'website_setting_id' => 1,
                'title' => 'Employee Performance Tracking',
                'description' => 'Monitor staff performance and track service reviews for improvements.',
                'image' => '/dummy-images/features/img5.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'website_setting_id' => 1,
                'title' => 'Multi-location Management',
                'description' => 'Manage multiple salon locations from a centralized dashboard.',
                'image' => '/dummy-images/features/img6.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'website_setting_id' => 1,
                'title' => 'Staff Earnings Overview',
                'description' => 'Track individual staff earnings based on completed appointments and tips.',
                'image' => '/dummy-images/features/img7.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'website_setting_id' => 1,
                'title' => 'Subscription Analytics',
                'description' => 'Monitor active subscriptions, renewals, and revenue generated from plans.',
                'image' => '/dummy-images/features/img8.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'website_setting_id' => 1,
                'title' => 'Earning Insights for Staff',
                'description' => 'Provide staff access to view their daily, weekly, and monthly earnings.',
                'image' => '/dummy-images/features/img9.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'website_setting_id' => 1,
                'title' => 'Membership Renewal Insights',
                'description' => 'Track membership renewal rates and notify clients nearing expiration.',
                'image' => '/dummy-images/features/img10.jpeg',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'website_setting_id' => 1,
                'title' => 'Service Management',
                'description' => 'Frezka’s Service Management feature allows salon admins to easily create, edit, and manage all salon services in one place.',
                'image' => '/dummy-images/features/img11.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'website_setting_id' => 1,
                'title' => 'Tax Management',
                'description' => 'Configure tax rules & apply to services or subscriptions.',
                'image' => '/dummy-images/features/img12.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('website_features')->insert($data);

    }
}
