<?php

return [
    /*
     * Add files as you would if you were calling themer add directly
     */

    /*
     * Default stylesheets to load
     */
    'styles'    => [
        // Loaded on every page
        'global'    => [
            [
                'key'       => 'semantic-css',
                'filename'  => 'semantic.min.css',
                'url'       => 'https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.2.13/',
                'order'     => 1,
            ],
            [
                'key'       => 'app-css',
                'filename'  => 'app.min.css',
                'order'     => 2,
            ],
        ],

        // Loaded when form response prepared
        'form' => [
            [
                'key'       => 'form-css',
                'filename'  => 'form.min.css',
                'order'     => 3,
            ],
        ],
    ],

    /*
     * Default scripts to loads
     */
    'scripts'   => [
        // Loaded on every page
        'global'    => [
            [
                'key'       => 'jquery-base',
                'filename'  => 'jquery.min.js',
                'url'       => 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/',
                'order'     => 1,
            ],
            [
                'key'       => 'semantic-js',
                'filename'  => 'semantic.min.js',
                'url'       => 'https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.2.13/',
                'order'     => 2,
            ],
            [
                'key'       => 'app-base-js',
                'filename'  => 'app.js',
                'order'     => 3,
            ],
        ],

        // Loaded when form response prepared
        'form' => [
            [
                'key'       => 'form-base-js',
                'filename'  => 'forms/form.js',
                'order'     => 4,
            ],
        ],
    ],
];
