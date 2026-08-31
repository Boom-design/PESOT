<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'philsms' => [
        'token'     => env('PHILSMS_TOKEN'),

        // Alphanumeric sender IDs (max 11 characters) cannot be replied to,
        // which is what makes these messages one-way.
        'sender_id' => env('PHILSMS_SENDER_ID', 'PESO'),

        // Off until the subscription exists. While false the gateway is never
        // called: the message is written to the log and the flow completes, so
        // the feature can be built and demonstrated without spending credits.
        'enabled'   => (bool) env('PHILSMS_ENABLED', false),

        'endpoint'  => env('PHILSMS_ENDPOINT', 'https://app.philsms.com/api/v3/sms/send'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // ── Ang NSRP scanner nga Python service (ocr_service/). Localhost ra siya
    // ── ug walay authentication, mao nga dili gyud siya angay ma-abot gikan sa
    // ── gawas — ang Laravel ra ang mo-tawag niya. Kung patay ang service,
    // ── mo-fail ang scan nga malumo ug manual gihapon ang form. ──
    'ocr' => [
        'url'     => env('OCR_SERVICE_URL', 'http://127.0.0.1:8001'),
        'timeout' => env('OCR_SERVICE_TIMEOUT', 180),
    ],

];
