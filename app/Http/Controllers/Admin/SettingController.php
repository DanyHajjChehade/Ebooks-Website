<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', [
            'setting' => Setting::query()->oldest('id')->first() ?? new Setting(Setting::defaults()),
        ]);
    }

    public function update(SettingRequest $request): RedirectResponse
    {
        $setting = Setting::query()->oldest('id')->first() ?? new Setting;
        $setting->fill($request->validated())->save(); // saving flushes the settings cache

        return redirect()->route('admin.settings.edit')->with('status', 'Settings saved.');
    }
}
