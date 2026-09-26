{{--
    Danger zone card for edit pages (DESIGN.md §4.20). When :blocked is a reason string, the
    button is aria-disabled and the reason replaces the consequence sentence (A2).
--}}
@props(['title', 'body', 'action', 'label', 'confirm', 'confirmBody', 'confirmOk', 'confirmCancel', 'blocked' => null])
<section {{ $attributes->class('card grid justify-items-start gap-3 p-5 sm:p-6') }} aria-labelledby="danger-h">
    <h2 class="h4" id="danger-h">{{ $title }}</h2>
    <p class="text-sm text-muted">{{ $blocked ?: $body }}</p>
    <form method="POST" action="{{ $action }}">
        @csrf
        @method('DELETE')
        <button class="btn btn-danger-quiet" type="submit" @if ($blocked) aria-disabled="true" aria-describedby="danger-h" @endif
            data-confirm="{{ $confirm }}" data-confirm-body="{{ $confirmBody }}" data-confirm-ok="{{ $confirmOk }}" data-confirm-cancel="{{ $confirmCancel }}" data-confirm-busy="Deleting…"><x-icon name="trash-2"/>{{ $label }}</button>
    </form>
</section>
