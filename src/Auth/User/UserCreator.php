<?php
namespace Laranix\Auth\User;

use Illuminate\Database\Eloquent\Model;

interface UserCreator
{
    /**
     * @param array|Settings $values
     * @return \Illuminate\Database\Eloquent\Model|\Laranix\Auth\User\User
     */
    public function createUser($values) : Model;
}
