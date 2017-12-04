<?php

use Carbon\Carbon;

$tracker = [
    601 => ['id' => 1, 'user' => 1, 'ip' => '1.1.1.1', 'method' => 'GET', 'url' => '/foo/bar', 'typeId' => 1, 'type' => 'login', 'itemId' => 1,
            'level' => 10, 'trackType' => 2, 'data' => 'foo bar', 'updated' => Carbon::now(), 'created' => Carbon::now()],

    602 => ['id' => 2, 'user' => 1, 'ip' => '1.1.1.2', 'method' => 'GET', 'url' => '/foo/baz', 'typeId' => 2, 'type' => 'logout', 'itemId' => 1,
            'level' => 10, 'trackType' => 2, 'data' => '**foo**', 'updated' => Carbon::now()->subMinutes(121), 'created' => Carbon::now()->subMinutes(125)],

    603 => ['id' => 3, 'user' => 1, 'ip' => '1.1.1.3', 'method' => 'GET', 'url' => '/bar/baz', 'typeId' => 3, 'type' => 'login', 'itemId' => 4,
            'level' => 100, 'trackType' => 4, 'data' => '_hello world_', 'updated' => Carbon::now()->subMinutes(10), 'created' => Carbon::now()->subMinutes(11)],

    604 => ['id' => 4, 'user' => 4, 'ip' => '1.1.1.4', 'method' => 'POST', 'url' => '/foo/bar', 'typeId' => 4, 'type' => 'track', 'itemId' => 4,
            'level' => 25, 'trackType' => 4, 'data' => 'foo _bar_ ~baz~', 'updated' => Carbon::now()->subMinutes(15), 'created' => Carbon::now()->subMinutes(15)],

    605 => ['id' => 5, 'user' => 5, 'ip' => '1.1.1.5', 'method' => 'POST', 'url' => '/foo/bar', 'typeId' => null, 'type' => 'login', 'itemId' => null,
            'level' => 20, 'trackType' => 2, 'data' => null, 'updated' => Carbon::now()->subMinutes(200), 'created' => Carbon::now()->subMinutes(205)],
];


/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(\Laranix\Tracker\Tracker::class, function (Faker\Generator $faker) use ($tracker) {

    $id     = $faker->unique()->numberBetween(601,605);
    $data   = $tracker[$id];

    return [
        'id'                => $data['id'],
        'user_id'           => $data['user'],
        'ipv4'              => ip2long($data['ip']),
        'user_agent'        => $faker->userAgent,
        'request_method'    => $data['method'],
        'request_url'       => $data['url'],
        'type'              => $data['type'],
        'type_id'           => $data['typeId'],
        'item_id'           => $data['itemId'],
        'level'             => $data['level'],
        'trackable_type'    => $data['trackType'],
        'data'              => $data['data'],
        'data_rendered'     => $data['data'] !== null && config('tracker.save_rendered', true) ? markdown($data['data']) : null,
        'created_at'        => $data['created'],
        'updated_at'        => $data['updated'],
    ];
});
