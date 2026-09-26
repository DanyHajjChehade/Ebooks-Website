<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class DatabaseSeeder extends Seeder
{
    /**
     * Demo data for local development and review:
     *   admin@bookplanet.test / password   (administrator)
     *   reader@bookplanet.test / password  (customer who owns 3 books)
     *
     * Safe to re-run: `php artisan migrate:fresh --seed`.
     *
     * Never runs in production: it would create an admin with a published
     * password. A live store needs no seed data (settings fall back to
     * defaults until saved in the admin area; create admins with
     * `php artisan app:make-admin`).
     */
    public function run(): void
    {
        if (app()->isProduction()) {
            $message = 'Demo data is not seeded in production (it would create a known-password admin).';
            Log::error($message);
            $this->command?->error($message);

            return;
        }

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
