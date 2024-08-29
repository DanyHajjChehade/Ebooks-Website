<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\SettingUpdateRequest;
use App\Models\Setting;
use App\Utils\ImageUpload;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;


class SettingController extends Controller
{
    public function index()
    {
        return view('dashboard.settings.index');
    }
    public function update(SettingUpdateRequest $request,Setting $setting)
    {
        $setting->update($request->validated());
        if ($request->hasFile('logo')) {
               $logo=ImageUpload::uploadimage($request->logo,100,200,'logo/');
               $setting->update(['logo'=>$logo]);
        }
        if ($request->hasFile('favicon')) {
            $favicon=ImageUpload::uploadimage($request->favicon,32,32,'logo/');
            $setting->update(['favicon'=>$favicon]);
        }

        return redirect()->route('dashboard.settings.index')->with('success', 'Settings updated successfully');
    }


}
