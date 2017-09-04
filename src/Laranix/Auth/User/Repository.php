<?php
namespace Laranix\Auth\User;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

interface Repository
{
    /**
     * Fetch a user.
     *
     * @param mixed $id
     * @return \Illuminate\Contracts\Auth\Authenticatable|\Illuminate\Contracts\Auth\Authenticatable|\Laranix\Auth\User\User|null
     */
    public function getUser($id = null): ?Authenticatable;

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  int $id
     * @return \Illuminate\Contracts\Auth\Authenticatable|\Illuminate\Contracts\Auth\Authenticatable|User|null
     */
    public function getById(int $id): ?Authenticatable;

    /**
     * @param string $email
     * @return \Illuminate\Contracts\Auth\Authenticatable|\Illuminate\Database\Eloquent\Builder|User|null
     */
    public function getByEmail(string $email): ?Authenticatable;

    /**
     * Retrieve a user by their unique identifier and token.
     *
     * @param  int    $id
     * @param  string $token
     * @param  int $type
     * @return \Illuminate\Contracts\Auth\Authenticatable|\Illuminate\Database\Eloquent\Builder|null
     */
    public function getByToken(int $id, string $token, int $type): ?Authenticatable;

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param  int    $id
     * @param  string $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|\Illuminate\Database\Eloquent\Builder|null
     */
    public function getByRememberToken(int $id, string $token): ?Authenticatable;

    /**
     * Retrieve a user by their unique identifier and api token.
     *
     * @param  int    $id
     * @param  string $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|\Illuminate\Database\Eloquent\Builder|null
     */
    public function getByApiToken(int $id, string $token): ?Authenticatable;

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|\Illuminate\Database\Eloquent\Builder|null
     */
    public function getByCredentials(array $credentials): ?Authenticatable;

    /**
     * @return \Illuminate\Database\Eloquent\Model|\Laranix\Auth\User\User
     */
    public function getModel(): Model;
}
