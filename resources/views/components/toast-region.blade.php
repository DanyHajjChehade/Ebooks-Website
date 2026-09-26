{{--
    Toast region + session flashes (status/success → success, error → error). Works without JS
    (just without auto-dismiss). Pass :except="['error']" when the page shows that flash inline.
--}}
@props(['except' => []])
<div class="toast-region" data-toast-region>
    @foreach (['status' => 'success', 'success' => 'success', 'error' => 'error'] as $key => $type)
        @if (! in_array($key, $except, true) && is_string(session($key)) && session($key) !== '')
            <x-toast :type="$type">{{ session($key) }}</x-toast>
        @endif
    @endforeach
</div>
<div id="announcer" class="sr-only" aria-live="polite" aria-atomic="true"></div>
<template id="tpl-toast">
    <div class="toast" data-toast>
        <span data-toast-icon></span>
        <div><span data-toast-message></span></div>
        <button class="btn btn-ghost btn-icon btn-sm" type="button" aria-label="Dismiss" data-toast-dismiss><x-icon name="x" class="icon-sm"/></button>
    </div>
</template>
<template id="tpl-icons">
    <x-icon name="circle-check" data-icon="circle-check"/>
    <x-icon name="circle-alert" data-icon="circle-alert"/>
    <x-icon name="info" data-icon="info"/>
    <x-icon name="shopping-bag" data-icon="shopping-bag"/>
    <x-icon name="loader-circle" class="btn-spinner" data-icon="loader-circle"/>
</template>
