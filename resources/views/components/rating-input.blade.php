{{-- Star rating input: fieldset + five radios in DOM order 1→5 (DESIGN.md §4.9). --}}
@props(['name' => 'rating', 'value' => null, 'id' => 'rating', 'bag' => 'default'])
@php
    $current = (int) old($name, $value);
    $error = ($errors ?? new \Illuminate\Support\ViewErrorBag)->getBag($bag)->first($name);
@endphp
<fieldset {{ $attributes->class('grid gap-2') }} id="{{ $id }}" @if ($error) aria-describedby="{{ $id }}-error" @endif>
    <legend class="label mb-2">Your rating</legend>
    <div class="rating-input__stars">
        @for ($i = 1; $i <= 5; $i++)
            <input class="sr-only" type="radio" name="{{ $name }}" id="{{ $id }}-{{ $i }}" value="{{ $i }}" @checked($current === $i) @if ($i === 1) required @endif>
            <label for="{{ $id }}-{{ $i }}"><x-icon name="star"/><span class="sr-only">{{ $i }} {{ $i === 1 ? 'star' : 'stars' }}</span></label>
        @endfor
    </div>
    @if ($error)<p class="field-error" id="{{ $id }}-error"><x-icon name="circle-alert"/>{{ $error }}</p>@endif
</fieldset>
