<?php
namespace Laranix\Auth\User;

use Carbon\Carbon;
use Laranix\Support\Exception\NullValueException;

trait UpdatesLastLogin
{
    /**
     * @var Carbon
     */
    protected $lastLoginParsed = null;

    /**
     * Get the property name
     *
     * @return string
     */
    public function getLastLoginKey() : string
    {
        return 'last_login';
    }

    /**
     * Get last login
     *
     * @return \Carbon\Carbon
     */
    public function getLastLogin() : Carbon
    {
        if ($this->lastLoginParsed !== null) {
            return $this->lastLoginParsed;
        }

        $name = $this->getLastLoginKey();

        $lastLogin = $this->asDateTime($this->getAttributeFromArray($name) ?? Carbon::now());

        return $this->{$name} = $this->lastLoginParsed = Carbon::createFromFormat('Y-m-d H:i:s', $lastLogin);
    }

    /**
     * Update a users last login
     *
     * @param \Carbon\Carbon|null $override
     * @return $this
     * @throws \Laranix\Support\Exception\NullValueException
     */
    public function updateLastLogin(?Carbon $override = null)
    {
        $name = $this->getLastLoginKey();

        if ($this->getAttributeFromArray($name) === null) {
            throw new NullValueException("Property {$name} not set on this object");
        }

        $this->{$name} = $override === null ? Carbon::now()->toDateTimeString() : $override->toDateTimeString();

        $this->lastLoginParsed = null;

        return $this;
    }
}
