<?php
namespace Laranix\Auth\Group;

use Illuminate\Database\Eloquent\Model;

interface GroupCreator
{
    /**
     * @param array|Settings $values
     * @return \Illuminate\Database\Eloquent\Model|\Laranix\Auth\Group\Group
     */
    public function createGroup($values): Model;
}
