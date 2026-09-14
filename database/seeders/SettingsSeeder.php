<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Services\Setting\SettingService;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Seed initial global website settings.
     */
    public function run(): void
    {
        $settingService = new SettingService;
        $definitions = $settingService->getSettingDefinitions();

        $initialValues = [
            // Identity
            'site_name' => 'Hanara CMS',
            'site_description' => 'A robust, scalable, and modular Content Management System built with pure Laravel 13, Tailwind CSS, and Clean Code principles.',
            'site_tagline' => 'Next-Gen Enterprise Content Engine',
            'site_logo' => '',
            'site_favicon' => '',

            // Contact & Location
            'contact_address' => 'Jl. Jenderal Sudirman Kav. 52-53, SCBD',
            'contact_city' => 'Jakarta Selatan',
            'contact_phone' => '+62 21 555 1234',
            'contact_whatsapp' => '+62 812 3456 7890',
            'contact_email' => 'contact@hanara-cms.local',
            'contact_hours' => 'Mon - Fri: 08:00 - 17:00 WIB',

            // Social Media
            'social_facebook' => 'https://facebook.com/hanaracms',
            'social_instagram' => 'https://instagram.com/hanaracms',
            'social_linkedin' => 'https://linkedin.com/company/hanaracms',
            'social_youtube' => 'https://youtube.com/@hanaracms',

            // Basic SEO
            'seo_meta_title' => 'Hanara CMS - Clean & Modular Laravel 13 CMS',
            'seo_meta_description' => 'Enterprise grade Content Management System with multilingual support, ACL roles, and editorial approval workflow.',
            'seo_meta_keywords' => 'laravel, cms, content management, php 8.3, tailwindcss',
        ];

        foreach ($definitions as $def) {
            Setting::firstOrCreate(
                ['key' => $def['key']],
                [
                    'value' => $initialValues[$def['key']] ?? $def['default'],
                    'group' => $def['group'],
                    'type' => $def['type'],
                    'label' => $def['label'],
                ]
            );
        }
    }
}
