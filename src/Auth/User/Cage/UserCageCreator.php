<?php
namespace Laranix\Auth\User\Cage;

interface UserCageCreator
{
    /**
     * @param array|Settings $values
     * @return \Illuminate\Database\Eloquent\Model|\Laranix\Auth\User\Cage\Cage
     */
    public function createUserCage($values) : Cage;
}
