<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $setting = Setting::query()->oldest('id')->first() ?? new Setting;

        $setting->fill([
            'site_name' => 'Book Planet',
            'tagline' => 'Independent ebooks, beautifully made.',
            'contact_email' => 'hello@bookplanet.test',
            'phone' => null,
            'address' => null,
            'facebook_url' => null,
            'instagram_url' => 'https://www.instagram.com/',
            'x_url' => null,
            'youtube_url' => null,
            'tiktok_url' => null,
        ])->save();
    }
}
