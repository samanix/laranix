<?php
namespace Laranix\Auth\Password;

use Laranix\Support\Exception\NullValueException;

trait HashesPasswords
{
    /**
     * @param string $password
     * @param int    $cost
     * @return string
     */
    public function hashUserPassword(string $password, ?int $cost = null) : string
    {
        return bcrypt($password, ['rounds' => $this->getPasswordCost($cost)]);
    }

    /**
     * Hash users password when property 'password' is set
     *
     * @param int|null $cost
     * @return string
     * @throws \Laranix\Support\Exception\NullValueException
     */
    public function hashUserPasswordProperty(?int $cost = null) : string
    {
        if (!isset($this->password)) {
            throw new NullValueException('Password property not set on this object');
        }

        return bcrypt($this->password, ['rounds' => $this->getPasswordCost($cost)]);
    }

    /**
     * Get password cost
     *
     * @param int|null $cost
     * @return int
     */
    public function getPasswordCost(?int $cost = null) : int
    {
        if ($cost !== null) {
            return $cost;
        }

        /** @var \Illuminate\Contracts\Config\Repository $config */
        $config = $this->config ?? config();

        return $config->get('laranixauth.password.cost', 12);
    }
}
