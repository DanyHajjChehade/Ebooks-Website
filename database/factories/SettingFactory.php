<?php

namespace Database\Factories;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Setting>
 */
class SettingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'site_name' => 'Book Planet',
            'tagline' => 'Independent ebooks, beautifully made.',
            'contact_email' => 'hello@bookplanet.test',
            'phone' => null,
            'address' => null,
            'facebook_url' => null,
            'instagram_url' => 'https://instagram.com/bookplanet',
            'x_url' => null,
            'youtube_url' => null,
            'tiktok_url' => null,
        ];
    }
}
