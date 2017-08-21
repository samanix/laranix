<?php
namespace Laranix\Auth\User;

use Illuminate\Database\Eloquent\Model;

trait CreatesUsers
{
    /**
     * @param array|Settings $values
     * @return \Illuminate\Database\Eloquent\Model|\Laranix\Auth\User\User
     */
    public function createUser($values) : Model
    {
        if (is_array($values)) {
            $values = new Settings($values);
        }

        $values->hasRequiredSettings();

        return User::createNew([
            'email'             => $values->email,
            'username'          => $values->username,
            'avatar'            => $values->avatar,
            'first_name'        => $values->first_name,
            'last_name'         => $values->last_name,
            'password'          => $values->hashUserPasswordProperty(),
            'company'           => $values->company,
            'timezone'          => $values->timezone,
            'account_status'    => $values->status,
        ]);
    }
}
