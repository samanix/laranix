<?php
namespace Laranix\Auth\User\Groups;

use Illuminate\Database\Eloquent\Model;

interface UserGroupAdder
{
    /**
     * @param array|Settings $values
     * @return \Illuminate\Database\Eloquent\Model|UserGroup
     */
    public function addUserToGroup($values) : Model;
}
