<?php

use Carbon\Carbon;

$groups = [
    101 => ['id' => 1, 'name' => 'Admin', 'color' => 'red', 'icon' => 'admin.png', 'level' => 100, 'flags' => ['a', 'b', 'c'], 'hidden' => 0, 'updated' => Carbon::now(), 'created' => Carbon::now()],
    102 => ['id' => 2, 'name' => 'Mod', 'color' => 'blue', 'icon' => 'mod.png', 'level' => 25, 'flags' => ['d', 'e', 'f'], 'hidden' => 0, 'updated' => Carbon::now(), 'created' => Carbon::now()->subMinutes(5)],
    103 => ['id' => 3, 'name' => 'User', 'color' => 'orange', 'icon' => 'user.png', 'level' => 10, 'flags' => [], 'hidden' => 0, 'updated' => Carbon::now()->subMinutes(30), 'created' => Carbon::now()->subMinutes(35)],
    104 => ['id' => 4, 'name' => 'Subadmin', 'color' => 'green', 'icon' => 'subadmin.jpg', 'level' => 75, 'flags' => ['x', 'y', 'z'], 'hidden' => 1, 'updated' => Carbon::now()->subMinutes(60), 'created' => Carbon::now()->subMinutes(65)],
    105 => ['id' => 5, 'name' => 'Manager', 'color' => 'purple', 'icon' => 'manager.jpg', 'level' => 50, 'flags' => ['post',  'delete'], 'hidden' => 1, 'updated' => Carbon::now()->subMinutes(120), 'created' => Carbon::now()->subMinutes(125)],
];


/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(\Laranix\Auth\Group\Group::class, function (Faker\Generator $faker) use ($groups) {

    $id     = $faker->unique()->numberBetween(101,105);
    $data   = $groups[$id];

    return [
        'group_id'      => $data['id'],
        'group_name'    => $data['name'],
        'group_color'   => $data['color'],
        'group_icon'    => $data['icon'],
        'group_level'   => $data['level'],
        'group_flags'   => json_encode($data['flags']),
        'is_hidden'     => $data['hidden'],
        'created_at'    => $data['created'],
        'updated_at'    => $data['updated'],
    ];
});
