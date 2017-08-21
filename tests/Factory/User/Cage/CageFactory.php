<?php

use Carbon\Carbon;

$tracker = [
    1401 => ['id' => 1, 'level' => 10, 'area' => 'login', 'time' => 5, 'reason' => '_foo_', 'issuer' => 1, 'user' => 3, 'ipv4' => '1.1.1.1', 'status' => 3, 'created' => Carbon::now()->subMinutes(5), 'updated' => Carbon::now()->subMinutes(5), 'deleted' => Carbon::now()],
    1402 => ['id' => 2, 'level' => 10, 'area' => 'foo', 'time' => 10, 'reason' => '**foobar**', 'issuer' => 1, 'user' => 3, 'ipv4' => '1.1.1.1', 'status' => 1, 'created' => Carbon::now(), 'updated' => Carbon::now()],
    1403 => ['id' => 3, 'level' => 15, 'area' => 'bar', 'time' => 30, 'reason' => 'foo', 'issuer' => 1, 'user' => 4, 'ipv4' => '1.1.1.3', 'status' => 2, 'created' => Carbon::now()->subMinutes(35), 'updated' => Carbon::now()->subMinutes(30)],
    1404 => ['id' => 4, 'level' => 20, 'area' => 'baz', 'time' => 1440, 'reason' => 'foo', 'issuer' => 1, 'user' => 4, 'status' => 1, 'created' => Carbon::now()->subMinutes(600), 'updated' => Carbon::now()->subMinutes(600)],
    1405 => ['id' => 5, 'level' => 25, 'area' => 'login', 'time' => 0, 'reason' => '**foo** _bar_', 'issuer' => 2, 'user' => 5, 'ipv4' => '1.1.1.5', 'status' => 1, 'created' => Carbon::now()->subMinutes(100), 'updated' => Carbon::now()->subMinutes(50)],
];


/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(\Laranix\Auth\User\Cage\Cage::class, function (Faker\Generator $faker) use ($tracker) {

    $id     = $faker->unique()->numberBetween(1401,1405);
    $data   = $tracker[$id];

    return [
        'cage_id'               => $data['id'],
        'cage_level'            => $data['level'],
        'cage_area'             => $data['area'],
        'cage_time'             => $data['time'],
        'cage_reason'           => $data['reason'],
        'cage_reason_rendered'  => markdown($data['reason']),
        'issuer_id'             => $data['issuer'],
        'user_id'               => $data['user'],
        'user_ipv4'             => isset($data['ipv4']) ? ip2long($data['ipv4']) : null,
        'cage_status'           => $data['status'],
        'created_at'            => $data['created'],
        'updated_at'            => $data['updated'],
        'deleted_at'            => $data['deleted'] ?? null,
    ];
});
