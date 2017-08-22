<?php

return [
    /*
     * Add files as you would if you were calling themer add directly
     */

    /*
     * Default stylesheets to load
     */
    'sheets'    => [
         'semantic-css' => [
            'file'  => 'semantic.min.css',
            'url'   => 'https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.2.11/',
            'order' => 1,
        ],
        'app-css'   => [
            'file'  => 'app.min.css',
            'order' => 2,
        ],
    ],

    /*
     * Default scripts to loads
     */
    'scripts'   => [
        'jquery' => [
            'file'  => 'jquery.min.js',
            'url'   => 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/',
            'order' => 1,
        ],
        'semantic-js' => [
            'file'  => 'semantic.min.js',
            'url'   => 'https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.2.11/',
            'order' => 1,
        ],
        'app-main' =>  [
            'file'  => 'app.js',
            'order' => 2,
        ],
    ],
];
