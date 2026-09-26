<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form fields: site_name, tagline, contact_email, phone, address, facebook_url, instagram_url,
 * x_url, youtube_url, tiktok_url.
 */
class SettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('access-admin') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $url = ['nullable', 'url:https,http', 'max:255'];

        return [
            'site_name' => ['required', 'string', 'max:100'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'facebook_url' => $url,
            'instagram_url' => $url,
            'x_url' => $url,
            'youtube_url' => $url,
            'tiktok_url' => $url,
        ];
    }
}
