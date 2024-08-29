<?php

namespace App\Utils;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
class ImageUpload
{

    public static function uploadimage($request,$height=null,$width=null,$path=null)
    {
        // Check if a file was uploaded with the name 'logo'

        // Get the original file extension
        $extension = $request->getClientOriginalExtension();
        // Generate a unique filename
        $imageName =Str::uuid().date('Y-m-d') . '.' . $extension;
        [$widthDefault,$heightDefault]=getimagesize($request);
        $height=$height ?? $heightDefault;
        $width=$width ?? $widthDefault;
        $image = Image::make($request->path());
        $image->fit($width,$height, function ($constraint) {
            $constraint->upsize();
        });

        // Save the image to the storage disk
        Storage::disk('images')->put($path.$imageName, $image->stream());
        return $path.$imageName;

    }
}
