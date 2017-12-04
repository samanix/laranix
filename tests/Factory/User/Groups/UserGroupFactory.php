<?php

$groups = [
    1101 => ['id' => 1, 'group' => 1, 'primary' => 1, 'hidden' => 0],
    1102 => ['id' => 1, 'group' => 2, 'primary' => 0, 'hidden' => 0],
    1103 => ['id' => 1, 'group' => 3, 'primary' => 0, 'hidden' => 0],
    1104 => ['id' => 2, 'group' => 2, 'primary' => 1, 'hidden' => 0],
    1105 => ['id' => 2, 'group' => 3, 'primary' => 0, 'hidden' => 0],
    1106 => ['id' => 3, 'group' => 3, 'primary' => 1, 'hidden' => 0],
    1107 => ['id' => 3, 'group' => 4, 'primary' => 0, 'hidden' => 1],
    1108 => ['id' => 4, 'group' => 3, 'primary' => 0, 'hidden' => 0],
    1109 => ['id' => 5, 'group' => 1, 'primary' => 1, 'hidden' => 0],
    1110 => ['id' => 5, 'group' => 5, 'primary' => 0, 'hidden' => 1],
];


/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(\Laranix\Auth\User\Groups\UserGroup::class, function (Faker\Generator $faker) use ($groups) {

    $id     = $faker->unique()->numberBetween(1101,1110);
    $data   = $groups[$id];

    return [
        'user_id'   => $data['id'],
        'group_id'  => $data['group'],
        'primary'   => $data['primary'],
        'hidden'    => $data['hidden'],
    ];
});
