<?php


$users = [
    1 => ['id' => 1, 'email' => 'foo@bar.com', 'username' => 'foo', 'fname' => 'Foo', 'lname' => 'Bar', 'co' => 'Foo Co', 'status' => 1, 'remember' => 'foo123', 'api' => '321foo'],
    2 => ['id' => 2, 'email' => 'bar@baz.com', 'username' => 'bar', 'fname' => 'Bar', 'lname' => 'Baz', 'co' => 'Bar Co', 'status' => 1, 'remember' => 'bar123', 'api' => '321bar'],
    3 => ['id' => 3, 'email' => 'foo@baz.com', 'username' => 'baz', 'fname' => 'Baz', 'lname' => 'Foo', 'co' => 'Baz Co', 'status' => 0, 'remember' => 'baz123', 'api' => '321baz'],
    4 => ['id' => 4, 'email' => 'baz@bar.com', 'username' => 'foobar', 'fname' => 'Santa', 'lname' => 'Claus', 'co' => 'FooBar Co', 'status' => 2, 'remember' => 'foo321', 'api' => '123foo'],
    5 => ['id' => 5, 'email' => 'baz@foo.com', 'username' => 'foobaz', 'fname' => 'Easter', 'lname' => 'Bunny', 'co' => 'FooBaz Co', 'status' => 3, 'remember' => 'bar321', 'api' => '123bar'],
];

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(\Laranix\Auth\User\User::class, function (Faker\Generator $faker) use ($users) {

    $id     = $faker->unique()->numberBetween(1,5);
    $data   = $users[$id];

    return [
        'id'                => $data['id'],
        'email'             => $data['email'],
        'username'          => $data['username'],
        'avatar'            => str_random(10),
        'first_name'        => $data['fname'],
        'last_name'         => $data['lname'],
        'password'          => bcrypt('secret'),
        'company'           => $data['co'],
        'timezone'          => $faker->timezone,
        'account_status'    => $data['status'],
        'remember_token'    => $data['remember'],
        'api_token'         => $data['api'],
    ];
});
