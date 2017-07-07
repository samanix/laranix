<?php

use Carbon\Carbon;

$verify = [
    1201 => ['id' => 1, 'email' => 'foo2@bar.com', 'token' => 'abc123', 'created' => Carbon::now(), 'updated' => Carbon::now()],
    1202 => ['id' => 2, 'email' => 'bar2@baz.com', 'token' => 'foo123', 'created' => Carbon::now()->subMinutes(5), 'updated' => Carbon::now()->subMinutes(1)],
    1203 => ['id' => 3, 'email' => 'foo2@baz.com', 'token' => 'foobar', 'created' => Carbon::now()->subMinutes(30), 'updated' => Carbon::now()->subMinutes(5)],
    1204 => ['id' => 4, 'email' => 'baz2@bar.com', 'token' => 'abcfoo', 'created' => Carbon::now()->subMinutes(120), 'updated' => Carbon::now()->subMinutes(120)],
    1205 => ['id' => 5, 'email' => 'baz2@foo.com', 'token' => 'token123', 'created' => Carbon::now()->subMinutes(180), 'updated' => Carbon::now()->subMinutes(30)],
];


/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(\Laranix\Auth\Email\Verification\Verification::class, function (Faker\Generator $faker) use ($verify) {

    $id     = $faker->unique()->numberBetween(1201,1205);
    $data   = $verify[$id];

    return [
        'user_id'       => $data['id'],
        'email'         => $data['email'],
        'token'         => hash('sha256', $data['token']),
        'created_at'    => $data['created'],
        'updated_at'    => $data['updated'],
    ];
});
