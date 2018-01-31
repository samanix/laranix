<?php

return [
    'user' => [
        'table'             => 'user',

        'views'             => [
            'register_form'     => 'auth.register.form',
            'register_success'  => 'auth.register.success',
        ],
    ],

    'groups' => [
        'table'         => 'groups',
        'default_group' => 'User', // Make sure it exists in groups table

        // If true, will use the json column type for the group flags
        // Only available since mysql 5.7
        'use_json_column'   => false,
    ],

    'usergroups' => [
        'table' => 'usergroups',
    ],

    'cage' => [
        'table' => 'user_cage',

        // As the data column stores markdown by default,
        // this option will allow you to store the rendered HTML.
        //
        // Storing like this can speed up loading and displaying of data
        'save_rendered'   => true,

        // Set aliases for areas
        // TODO
        //'alias' => [
            // 'l' => 'login',
        //],
    ],

    'verification'  => [
        'table'     => 'email_verification',
        'route'     => 'email.verify',    // Route name to verify token
        'expiry'    => 60,          // Time in minutes before token expires

        'mail'      => [
            'view'      => 'mail.auth.verification',
            'subject'   => 'Laranix Email Verification',
            'markdown'  => true,
        ],

        'views'     => [
            'verify_form'       => 'auth.verify.verify',
            'verify_refresh'    => 'auth.verify.refresh',
        ],
    ],

    'password'  => [
        'cost'      => 12,
        'table'     => 'password_reset',
        'route'     => 'password.reset',    // Route name to verify token
        'expiry'    => 60,                  // Time in minutes before token expires

        'mail'      => [
            'view'      => 'mail.auth.reset',
            'subject'   => 'Laranix Password Reset',
            'markdown'  => true,
        ],

        'views'     => [
            'request_form'  => 'auth.password.forgot',
            'reset_form'    => 'auth.password.reset',
        ],
    ],

    'login' => [
        // TODO
        //'allowed_attempts'  => 3,       // Number of login attempts before locking out temporarily
        //'lockout_time'      => 300,     // Lockout time

        'views'     => [
            'login_form'    => 'auth.login',
        ],
    ],
];
