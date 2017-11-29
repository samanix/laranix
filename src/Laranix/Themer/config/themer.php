<?php

return [
    // Theme List
    // For web path and path, use relative or full paths, do not use helpers
    'themes' => [
        'laranix' => [
            'name'      => 'Laranix',
            'path'      => 'themes/laranix',    // Relative to public_path()
            'webPath'   => 'themes/laranix',    // Relative to app.url
            'enabled'   => true,
            'default'   => true,
            'override'  => false,
            'automin'   => false, // If true, will look for a file.min.ext in same directory (Local only)
        ],
    ],

    // Views
    'views' => [
        'style'     => 'layout.themer.style',
        'scripts'   => 'layout.themer.scripts',
    ],

    // Cookie Name
    'cookie' => 'laranix_theme',

    // Directory permissions
    'umask' => 0755,

    // Ignored environments
    // If your environment is listed below is active, then themer will just return
    // uncombined files to. Useful for development.
    'ignored' => [
        'testing',
        'local',
        'debug',
        'dev',
        'development',
    ],
];
