{{-- Theme segmented control: System / Paper / Night (aria-pressed, JS in theme.js). --}}
<div {{ $attributes->class('segmented js-only') }} role="group" aria-label="Theme" data-theme-switch>
    <button type="button" data-theme-choice="system" aria-pressed="true">System</button>
    <button type="button" data-theme-choice="light" aria-pressed="false"><x-icon name="sun" class="icon-sm"/>Paper</button>
    <button type="button" data-theme-choice="dark" aria-pressed="false"><x-icon name="moon" class="icon-sm"/>Night</button>
</div>
