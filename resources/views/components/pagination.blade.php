{{-- "Showing 25–48 of 128" + pagination links. <x-pagination :paginator="$books"/> --}}
@props(['paginator'])
@if ($paginator->total() > 0 && $paginator->hasPages())
    <div {{ $attributes->class('flex flex-col items-center gap-4 sm:flex-row sm:justify-between') }}>
        <p class="text-sm text-muted">Showing {{ number_format($paginator->firstItem()) }}–{{ number_format($paginator->lastItem()) }} of {{ number_format($paginator->total()) }}</p>
        {{ $paginator->onEachSide(1)->links() }}
    </div>
@elseif ($paginator->total() > 0)
    <p {{ $attributes->class('text-sm text-muted') }}>Showing {{ number_format($paginator->firstItem()) }}–{{ number_format($paginator->lastItem()) }} of {{ number_format($paginator->total()) }}</p>
@endif
