<?php

return [
    // App version
    'version' => env('APP_VERSION', '1.0.0'),

    // Views
    // Global views for success/error results
    'success_view'  => 'state.success',
    'error_view'    => 'state.error',

    'fbadmins'  => env('FB_ADMINS'),
    'fbappid'   => env('FB_APPID'),

    'analytics' => env('G_ANALYTICS'),

//    // Mail defaults
//    // Unused currently
//    'mail' => [
//
//        // Default "FROM" email
//        'from' => [
//            'address' => 'from@email.com',
//            'name' => 'Samanix',
//        ],
//
//        // Default "TO" email
//        'to' => [
//            'address' => 'to@email.com',
//            'name' => 'Samanix',
//        ],
//
//        // Default "NO REPLY" email
//        'nobody' => [
//            'address' => 'noreply@email.com',
//            'name' => 'Samanix',
//        ],
//    ],
];
