{{-- Brand planet mark: one sprite per page. Use <svg><use href="#bp-planet"/></svg>. --}}
<svg class="absolute size-0 overflow-hidden" aria-hidden="true" focusable="false">
    <defs>
        <mask id="bp-planet-cut" maskUnits="userSpaceOnUse" x="0" y="0" width="32" height="32">
            <rect width="32" height="32" fill="#fff"/>
            <path d="M3.21 21.69A14 4.5 -24 0 0 28.79 10.31" fill="none" stroke="#000" stroke-width="4.6"/>
        </mask>
    </defs>
    <symbol id="bp-planet" viewBox="0 0 32 32">
        <path d="M3.21 21.69A14 4.5 -24 0 1 28.79 10.31" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
        <circle cx="16" cy="16" r="8.25" fill="currentColor" mask="url(#bp-planet-cut)"/>
        <path d="M3.21 21.69A14 4.5 -24 0 0 28.79 10.31" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
    </symbol>
</svg>
