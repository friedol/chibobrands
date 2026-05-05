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

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_AUTH_TOKEN'),
        'from' => env('TWILIO_PHONE'),
        'api_key' => env('TWILIO_API_KEY'),
        'api_secret' => env('TWILIO_API_SECRET'),
    ],

    'sms' => [
        'api_key' => env('SMS_API_KEY', '2b5add88144ffb3b'),
        'api_secret' => env('SMS_API_SECRET', 'NDAwNDU3MGZkZGU1MjZkZWUwMjJiOTYzZTQ5YzQ0NmVmZGMxNWY3NGY0N2E3ZjM4OWE3OWYxODE0ODM4OGNmOA=='), // Beem Africa Secret Key (base64 encoded as provided)
        'api_url' => env('SMS_API_URL', 'https://apisms.beem.africa/v1/send'),
        'sender_id' => env('SMS_SENDER_ID', 'INFO'), // Default Beem sender or your approved sender ID
    ],

];
