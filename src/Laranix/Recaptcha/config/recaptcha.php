<?php

return [
    // Turn on/off
    'enabled'   => true,

    // Key and secret, obtain from Google
    'key'       => env('RECAPTCHA_KEY'),
    'secret'    => env('RECAPTCHA_SECRET'),

    // Default view to use
    'view'      => 'layout.recaptcha',

    // If true, recaptcha is disabled for logged in users
    'guests_only'    => false,

    // List of environments where Recaptcha is disabled
    'disabled_in'   => [
        'dev',
        'development',
        'homestead',
        'local',
        'testing',
        'debug',
    ],
];
