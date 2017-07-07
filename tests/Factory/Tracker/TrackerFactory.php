<?php

use Carbon\Carbon;

$tracker = [
    601 => ['id' => 1, 'user' => 1, 'ip' => '1.1.1.1', 'method' => 'GET', 'url' => '/foo/bar', 'typeId' => 1, 'type' => 'login', 'itemId' => 1,
            'flagLevel' => 10, 'trackType' => 2, 'data' => 'foo bar', 'updated' => Carbon::now(), 'created' => Carbon::now()],

    602 => ['id' => 2, 'user' => 1, 'ip' => '1.1.1.2', 'method' => 'GET', 'url' => '/foo/baz', 'typeId' => 2, 'type' => 'logout', 'itemId' => 1,
            'flagLevel' => 10, 'trackType' => 2, 'data' => '**foo**', 'updated' => Carbon::now()->subMinutes(121), 'created' => Carbon::now()->subMinutes(125)],

    603 => ['id' => 3, 'user' => 1, 'ip' => '1.1.1.3', 'method' => 'GET', 'url' => '/bar/baz', 'typeId' => 3, 'type' => 'login', 'itemId' => 4,
            'flagLevel' => 100, 'trackType' => 4, 'data' => '_hello world_', 'updated' => Carbon::now()->subMinutes(10), 'created' => Carbon::now()->subMinutes(11)],

    604 => ['id' => 4, 'user' => 4, 'ip' => '1.1.1.4', 'method' => 'POST', 'url' => '/foo/bar', 'typeId' => 4, 'type' => 'track', 'itemId' => 4,
            'flagLevel' => 25, 'trackType' => 4, 'data' => 'foo _bar_ ~baz~', 'updated' => Carbon::now()->subMinutes(15), 'created' => Carbon::now()->subMinutes(15)],

    605 => ['id' => 5, 'user' => 5, 'ip' => '1.1.1.5', 'method' => 'POST', 'url' => '/foo/bar', 'typeId' => null, 'type' => 'login', 'itemId' => null,
            'flagLevel' => 20, 'trackType' => 2, 'data' => null, 'updated' => Carbon::now()->subMinutes(200), 'created' => Carbon::now()->subMinutes(205)],
];


/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(\Laranix\Tracker\Tracker::class, function (Faker\Generator $faker) use ($tracker) {

    $id     = $faker->unique()->numberBetween(601,605);
    $data   = $tracker[$id];

    return [
        'tracker_id'        => $data['id'],
        'user_id'           => $data['user'],
        'ipv4'              => ip2long($data['ip']),
        'user_agent'        => $faker->userAgent,
        'request_method'    => $data['method'],
        'request_url'       => $data['url'],
        'tracker_type'      => $data['type'],
        'tracker_type_id'   => $data['typeId'],
        'tracker_item_id'   => $data['itemId'],
        'flag_level'        => $data['flagLevel'],
        'trackable_type'    => $data['trackType'],
        'tracker_data'      => $data['data'],
        'tracker_data_rendered' => $data['data'] !== null && config('tracker.save_rendered', true) ? markdown($data['data']) : null,
        'created_at'        => $data['created'],
        'updated_at'        => $data['updated'],
    ];
});
