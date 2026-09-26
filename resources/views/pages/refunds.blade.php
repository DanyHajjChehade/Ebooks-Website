{{-- Refund policy (A5). PLACEHOLDER copy: the shop owner must replace it before launch. --}}
@php
    $email = $settings->contact_email ?: 'the address on our contact page';
@endphp
<x-legal-page title="Refund policy" eyebrow="Help" noun="refund policy" updated="2026-09-26" description="When you can get a refund for an ebook, and how to ask for one." :sections="[
    ['right-to-cancel', 'Digital content and your right to cancel', ['Ebooks are digital content. When you check out you ask for immediate access, so the usual 14-day right to cancel ends once the download is available.']],
    ['faulty-files', 'Faulty files', ['If a file won’t open, is incomplete or isn’t the book described, tell us within 30 days. We will send a working file or refund you in full.']],
    ['mistakes', 'Duplicate or mistaken purchases', ['Bought the same book twice, or the wrong one? Contact us within 14 days and, if you haven’t downloaded it, we will refund it.']],
    ['how-to-ask', 'How to ask for a refund', ['Write to '.$email.' with your order number (it starts with BP-) and a short description of the problem.']],
    ['how-paid', 'How refunds are paid', ['Refunds go back to the card you paid with, usually within 5–10 working days. Refunded books leave your library.']],
]">
    <div class="callout mb-10 max-w-prose">
        <p class="eyebrow">The short version</p>
        <p class="font-serif text-lg">You can’t return an ebook once it’s downloadable, but if a file is faulty or you bought something by mistake, we’ll put it right. Write to us with your order number.</p>
    </div>
</x-legal-page>
