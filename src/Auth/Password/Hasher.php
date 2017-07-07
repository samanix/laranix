<?php
namespace Laranix\Auth\Password;

interface Hasher
{
    /**
     * @param string $password
     * @param int    $cost
     * @return string
     */
    public function hashUserPassword(string $password, int $cost = 10) : string;
}
