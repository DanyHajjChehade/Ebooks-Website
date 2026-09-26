<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'stripe' => [
        // Publishable key (pk_...) — only needed if the frontend ever loads Stripe.js.
        'key' => env('STRIPE_KEY'),
        // Secret key (sk_...) — server-side API calls (Checkout Sessions).
        'secret' => env('STRIPE_SECRET'),
        // Signing secret (whsec_...) for POST /stripe/webhook.
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        // Maximum age (seconds) of a webhook signature timestamp.
        'webhook_tolerance' => (int) env('STRIPE_WEBHOOK_TOLERANCE', 300),
        // ISO currency code used for every order (lowercase, e.g. usd, eur, gbp).
        'currency' => strtolower((string) env('STRIPE_CURRENCY', 'usd')),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
