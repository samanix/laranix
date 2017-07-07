<?php

use Carbon\Carbon;

$sessions = [
    501 => ['id' => 1, 'user' => 1, 'ip' => '1.1.1.1', 'data' => ['foo' => 'bar', 'url' => 'http://foo.com'], 'updated' => Carbon::now(), 'created' => Carbon::now()->subMinutes(5)],
    502 => ['id' => 2, 'user' => 2, 'ip' => '1.1.1.2', 'data' => ['foo' => 'bar', 'url' => 'http://bar.com'], 'updated' => Carbon::now()->subMinutes(121), 'created' => Carbon::now()->subMinutes(200)],
    503 => ['id' => 3, 'user' => 3, 'ip' => '1.1.1.3', 'data' => ['foo' => 'baz', 'url' => 'http://baz.com'], 'updated' => Carbon::now()->subMinutes(5), 'created' => Carbon::now()->subMinutes(5)],
    504 => ['id' => 4, 'user' => 4, 'ip' => '1.1.1.4', 'data' => ['bar' => 'baz', 'url' => 'http://foo.com/bar'], 'updated' => Carbon::now()->subMinutes(10), 'created' => Carbon::now()->subMinutes(15)],
    505 => ['id' => 5, 'user' => 5, 'ip' => '1.1.1.5', 'data' => ['foo' => 'barbaz', 'url' => 'http://foo.com/bar/baz'], 'updated' => Carbon::now()->subMinutes(300), 'created' => Carbon::now()->subMinutes(301)],
];


/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(\Laranix\Session\Session::class, function (Faker\Generator $faker) use ($sessions) {

    $id     = $faker->unique()->numberBetween(501,505);
    $data   = $sessions[$id];

    return [
        'session_id'    => $data['id'],
        'user_id'       => $data['user'],
        'ipv4'          => ip2long($data['ip']),
        'user_agent'    => $faker->userAgent,
        'session_data'  => base64_encode(serialize($data['data'])),
        'updated_at'    => $data['updated'],
        'created_at'    => $data['created'],
    ];
});
