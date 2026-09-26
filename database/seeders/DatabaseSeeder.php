<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Demo data for local development and review:
     *   admin@bookplanet.test / password   (administrator)
     *   reader@bookplanet.test / password  (customer who owns 3 books)
     *
     * Safe to re-run: `php artisan migrate:fresh --seed`.
     */
    public function run(): void
    {
        $this->user('Ada Planet', 'admin@bookplanet.test', admin: true);
        $this->user('Rowan Reader', 'reader@bookplanet.test');

        $this->call([
            SettingSeeder::class,
            CatalogueSeeder::class,
            DemoCustomerSeeder::class,
        ]);

        Setting::flushCache();
    }

    private function user(string $name, string $email, bool $admin = false): User
    {
        $user = User::query()->firstOrNew(['email' => $email]);
        $user->fill(['name' => $name, 'password' => 'password']);
        $user->is_admin = $admin;
        $user->email_verified_at ??= now();
        $user->save();

        return $user;
    }
}
