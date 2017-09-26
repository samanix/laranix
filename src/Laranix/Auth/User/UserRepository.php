<?php
namespace Laranix\Auth\User;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UserRepository implements Repository
{
    const TOKEN_TYPE_REMEMBER = 1;
    const TOKEN_TYPE_API = 2;

    /**
     * Fetch a user.
     *
     * @param mixed $id
     * @return \Illuminate\Contracts\Auth\Authenticatable|\Illuminate\Contracts\Auth\Authenticatable|\Laranix\Auth\User\User|null
     */
    public function getUser($id = null) : ?Authenticatable
    {
        if ($id === null) {
            return $this->getModel();
        }

        if (is_int($id)) {
            return $this->getById($id);
        }

        if (filter_var($id, FILTER_VALIDATE_EMAIL) !== false) {
            return $this->getByEmail($id);
        }

        return $this->getModel();
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  int $id
     * @return \Illuminate\Contracts\Auth\Authenticatable|\Illuminate\Contracts\Auth\Authenticatable|User|null
     */
    public function getById(int $id) : ?Authenticatable
    {
        return $this->getModel()->newQuery()->find($id);
    }

    /**
     * @param string $email
     * @return \Illuminate\Contracts\Auth\Authenticatable|\Illuminate\Database\Eloquent\Builder|User|null
     */
    public function getByEmail(string $email) : ?Authenticatable
    {
        return $this->getModel()
                    ->newQuery()
                    ->where('email', $email)
                    ->first();
    }

    /**
     * Retrieve a user by their unique identifier and token.
     *
     * @param  int    $id
     * @param  string $token
     * @param  int $type
     * @return \Illuminate\Contracts\Auth\Authenticatable|\Illuminate\Database\Eloquent\Builder|User|null
     */
    public function getByToken(int $id, string $token, int $type = self::TOKEN_TYPE_REMEMBER) : ?Authenticatable
    {
        return $type === self::TOKEN_TYPE_REMEMBER ?  $this->getByRememberToken($id, $token) : $this->getByApiToken($id, $token);
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param  int  $id
     * @param  string $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|\Illuminate\Database\Eloquent\Builder|User|null
     */
    public function getByRememberToken(int $id, string $token) : ?Authenticatable
    {
        $model = $this->getModel();

        $user = $model->newQuery()
                      ->where($model->getAuthIdentifierName(), $id)
                      ->first();

        if ($user === null) {
            return null;
        }

        $rememberToken = $user->getRememberToken();

        return !empty($rememberToken) && hash_equals($rememberToken, $token) ? $user : null;
    }

    /**
     * Retrieve a user by their unique identifier and api token.
     *
     * @param  int  $id
     * @param  string $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|\Illuminate\Database\Eloquent\Builder|User|null
     */
    public function getByApiToken(int $id, string $token) : ?Authenticatable
    {
        $model = $this->getModel();

        $user = $model->newQuery()
                      ->where($model->getAuthIdentifierName(), $id)
                      ->first();

        if ($user === null) {
            return null;
        }

        $apiToken = $user->getApiToken();

        return !empty($apiToken) && hash_equals($apiToken, $token) ? $user : null;
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|\Illuminate\Database\Eloquent\Builder|User|null
     */
    public function getByCredentials(array $credentials) : ?Authenticatable
    {
        if (empty($credentials)) {
            return null;
        }

        // First we will add each credential element to the query as a where clause.
        // Then we can execute the query and, if we found a user, return it in a
        // Eloquent User "model" that will be utilized by the Guard instances.
        $query = $this->getModel()->newQuery();

        foreach ($credentials as $key => $value) {
            if (! Str::contains($key, 'password')) {
                $query->where($key, $value);
            }
        }

        return $query->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model|\Laranix\Auth\User\User
     */
    public function getModel() : Model
    {
        return new User();
    }
}
