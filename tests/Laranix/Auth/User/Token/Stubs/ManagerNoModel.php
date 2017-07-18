<?php
namespace Laranix\Tests\Laranix\Auth\User\Stubs\Token\Stubs;

use Illuminate\Contracts\Auth\Authenticatable;
use Laranix\Auth\User\Token\Manager as BaseManager;
use Laranix\Auth\User\User;

class ManagerNoModel extends BaseManager
{
    protected $configKey = 'password';

    /**
     * Update user after token verified
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable|User $user
     * @param string                                          $email
     * @param mixed                                           $extra
     * @return \Illuminate\Contracts\Auth\Authenticatable|\Laranix\Auth\User\User
     */
    protected function updateUser(Authenticatable $user, string $email, $extra = null): Authenticatable
    {
        // TODO: Implement updateUser() method.
    }

    /**
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param string                                     $email
     * @return mixed
     */
    protected function canInsertToken(Authenticatable $user, string $email)
    {
        // TODO: Implement preInsertValidation() method.
    }
}
