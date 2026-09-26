@extends('layouts.app')
@section('title', 'Settings · Admin')
@section('content')
    <h1>Settings</h1>
    @if ($errors->any())<ul role="alert">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
    <form method="POST" action="{{ route('admin.settings.update') }}">@csrf @method('PUT')
        <label>Site name <input name="site_name" value="{{ old('site_name', $setting->site_name) }}" required maxlength="100"></label>
        <label>Tagline <input name="tagline" value="{{ old('tagline', $setting->tagline) }}" maxlength="255"></label>
        <label>Contact email <input type="email" name="contact_email" value="{{ old('contact_email', $setting->contact_email) }}"></label>
        <label>Phone <input name="phone" value="{{ old('phone', $setting->phone) }}" maxlength="50"></label>
        <label>Address <textarea name="address" maxlength="500">{{ old('address', $setting->address) }}</textarea></label>
        @foreach (\App\Models\Setting::SOCIAL_FIELDS as $field)
            <label>{{ \Illuminate\Support\Str::headline(str_replace('_url', '', $field)) }} URL <input type="url" name="{{ $field }}" value="{{ old($field, $setting->{$field}) }}"></label>
        @endforeach
        <button type="submit">Save settings</button>
    </form>
@endsection
