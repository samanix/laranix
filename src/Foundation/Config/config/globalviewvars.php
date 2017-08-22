<?php

return [
    /*
     * Add variables that will be shared with all views
     *
     * The key is the name of the variable in the view
     * The value is the alias for the container to make
     */
    'appsettings'   => AppSettings::class,
    'auth'          => 'auth.driver',
    'config'        => 'config',
];
