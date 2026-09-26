<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Upload limits
    |--------------------------------------------------------------------------
    |
    | Maximum sizes (in kilobytes) accepted by the admin upload forms. Make sure
    | PHP's `upload_max_filesize` / `post_max_size` and your web server's body
    | limit (e.g. nginx `client_max_body_size`) are at least this large.
    |
    */

    'max_ebook_kb' => (int) env('EBOOK_MAX_KB', 51200),

    'max_image_kb' => (int) env('IMAGE_MAX_KB', 2048),

    /*
    |--------------------------------------------------------------------------
    | Storage disks
    |--------------------------------------------------------------------------
    |
    | Ebook files are stored on a private disk and only ever streamed through
    | the authorised download route. Covers and author photos are public.
    |
    */

    'ebook_disk' => env('EBOOK_DISK', 'local'),

    'media_disk' => env('MEDIA_DISK', 'public'),

    /*
    |--------------------------------------------------------------------------
    | Catalogue
    |--------------------------------------------------------------------------
    */

    'per_page' => 12,

];
