<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Setting;
use Illuminate\Support\Facades\Blade;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Blade::if('admin', function () {
            return auth()->check() && auth()->user()->usertype == 1;
        });

        if(!app()->runningInConsole()){
            $setting = Setting::firstOr(function () {
                return Setting::create([
                     'name' => 'Book Planet',
                     'description' => "Explore the books we're reading, the joy of shared knowledge, and the events that inspire and delight us.",
                     'logo'=>'logo/86b48e77-4d03-4346-8e40-7bd6f6c9d93e2024-06-05.png',
                     'favicon'=>'logo/b15fb0b5-ffef-42a0-b8fb-ea33115175322024-06-05.png',
                     'email'=>'bookplanet@gmail.com',
                     'phone'=>'+961 70 70 70 80',
                     'address'=>'chhim,mont-lebanon,lebanon',
                     'facebook'=>'bookplanet',
                     'twitter'=>'bookplanet',
                     'instagram'=>'bookplanet',
                     'youtube'=>'bookplanet',
                     'tiktok'=>'bookplanet'
                 ]);
              });


              view()->share('setting', $setting);
            }
    }
}
