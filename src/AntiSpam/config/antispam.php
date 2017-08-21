<?php

return [

    /*
     * Sequence numbers
     */
    'sequence' => [
        'enabled'       => true,
        'view'          => 'layout.antispam.sequence',
        'field_name'    => '__sequence_id',

        // Disabled envs
        'disabled_env' => [
            'dev',
            'development',
            'homestead',
            'local',
            'testing',
            'debug',
        ],
    ],

    /*
     * Recaptcha
     */
    'recaptcha' => [
        'enabled'   => true,
        'view'      => 'layout.antispam.recaptcha',
        'key'       => env('RECAPTCHA_KEY'),
        'secret'    => env('RECAPTCHA_SECRET'),

        // Disabled envs
        'disabled_env' => [
            'dev',
            'development',
            'homestead',
            'local',
            'testing',
            'debug',
        ],

        // If true, will force all users to complete
        // Otherwise, will allow logged in users to skip
        'guests_only' => false,
    ],
];
