{{-- Terms of service (A5). PLACEHOLDER copy: the shop owner must replace it before launch. --}}
@php
    $shop = $settings->site_name ?: 'Book Planet';
    $email = $settings->contact_email ?: 'the address on our contact page';
@endphp
<x-legal-page title="Terms of service" noun="terms" updated="2026-09-26" :description="'The terms for buying and reading ebooks from '.$shop.'.'" :sections="[
    ['who-we-are', 'Who we are', [$shop.' is an independent shop selling ebooks in EPUB and PDF formats. These terms apply whenever you browse the shop, create an account or buy a book.']],
    ['your-account', 'Your account', ['You need an account to buy books and keep them in your library. Keep your password private; you are responsible for activity on your account.', 'You can delete your account at any time from your profile. We keep order records for our accounts, as the law requires.']],
    ['buying-ebooks', 'Buying ebooks', ['When you check out, you buy a licence to read the ebook, not the copyright in it. Your purchase is confirmed once your payment clears.']],
    ['prices-and-payment', 'Prices and payment', ['Prices are shown in '.\App\Support\Money::currency().' and include any discount displayed on the book page. Payments are processed by Stripe; we never see or store your card number.']],
    ['your-licence', 'Your licence to read', ['You may download and read your ebooks on your own devices for personal, non-commercial use.', ['Do not share, resell or publish the files.', 'Do not remove any notices from the files.']]],
    ['cancellations', 'Cancellations and refunds', ['Ebooks are digital content. When you ask for immediate access at checkout, you accept that you cannot cancel for a refund once the download is available. See our refund policy for the exceptions.']],
    ['liability', 'Our liability', ['We take care to deliver complete, readable files. If a file is faulty, we will replace it or refund you. Nothing in these terms limits rights you have under consumer law.']],
    ['changes', 'Changes to these terms', ['We may update these terms. The date at the top of this page shows when they last changed.']],
    ['contact', 'Contact', ['Questions about these terms? Write to '.$email.'.']],
]"/>
