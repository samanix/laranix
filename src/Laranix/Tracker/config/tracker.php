<?php

return [
    // Enable/disable tracker
    'enabled' => true,

    'table'   => 'tracker',

    // Buffer size
    // If -1, tracks will only be written to the database just before the app terminates
    // If 0, tracks will be written immediately
    // If >0, tracks will be filled up to the number before being written, then reset and continue
    //
    // Its worth noting that if you use -1 and your app does not terminate as intended, you will not write any tracks to the database
    'buffer' => 0,

    // As the data column stores markdown by default,
    // this option will allow you to store the rendered HTML.
    //
    // Storing like this can speed up loading and displaying of data
    'save_rendered'   => true,

    // Set aliases for tracker types
    'aliases'   => [
        // 'ban'    => 'App\Ban', // Example
    ],
];
