{{-- Privacy policy (A5). PLACEHOLDER copy: the shop owner must replace it before launch. --}}
@php
    $shop = $settings->site_name ?: 'Book Planet';
    $email = $settings->contact_email ?: 'the address on our contact page';
@endphp
<x-legal-page title="Privacy policy" noun="privacy policy" updated="2026-09-26" :description="'How '.$shop.' collects, uses and protects your personal data.'" :sections="[
    ['what-we-collect', 'What we collect', ['Your name, email address and password (stored as a secure hash) when you create an account; the books you buy and your order history; reviews you choose to publish.']],
    ['why', 'Why we collect it', ['To run your account and library, to process orders and refunds, to answer your questions and to keep the shop secure.']],
    ['payments', 'Payments (Stripe)', ['Card payments are handled by Stripe. Stripe receives your payment details directly; we only receive a confirmation and a reference for the payment.']],
    ['cookies', 'Cookies', ['We use essential cookies to keep you signed in, remember your cart and protect forms. We do not use advertising cookies.']],
    ['retention', 'How long we keep data', ['We keep your account data until you delete your account. We keep order records for as long as tax law requires, without your library or reviews.']],
    ['your-rights', 'Your rights', ['You can see, correct or delete your personal data. Most of this is available in your profile; for anything else, contact us.']],
    ['contact', 'Contact', ['Questions about your privacy? Write to '.$email.'.']],
]"/>
