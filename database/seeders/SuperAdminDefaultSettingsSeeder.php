<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SuperAdminDefaultSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Super Admin user ID (assuming it's 1)
        $superAdminId = 1;
        
        // Default settings for Super Admin
        $defaultSettings = [
            'app_name' => 'Frezka SaaS',
            'footer_text' => 'Built with ♥ from <a href="https://iqonic.design" target="_blank">IQONIC DESIGN.</a>',
            'helpline_number' => '+256',
            'copyright_text' => 'Copyright © 2024',
            'ui_text' => 'UI Powered By <a href="https://hopeui.iqonic.design/" target="_blank">HOPE UI</a>',
            'inquriy_email' => 'frezka@admin.com',
            'site_description' => 'Professional SaaS Platform',
            'logo' => 'img/logo/logo.png',
            'mini_logo' => 'img/logo/mini_logo.png',
            'dark_logo' => 'img/logo/dark_logo.png',
            'dark_mini_logo' => 'img/logo/mini_logo.png',
            'favicon' => 'img/logo/mini_logo.png',
            'bussiness_address_line_1' => '',
            'bussiness_address_line_2' => '',
            'bussiness_address_country' => '',
            'bussiness_address_state' => '',
            'bussiness_address_city' => '',
            'bussiness_address_postal_code' => '',
            'bussiness_address_latitude' => '',
            'bussiness_address_longitude' => '',
        ];
        
        foreach ($defaultSettings as $key => $value) {
            Setting::updateOrCreate(
                [
                    'name' => $key,
                    'created_by' => $superAdminId
                ],
                [
                    'val' => $value,
                    'type' => 'string'
                ]
            );
        }
        
        $this->command->info('Super Admin default settings created successfully!');
    }
}

