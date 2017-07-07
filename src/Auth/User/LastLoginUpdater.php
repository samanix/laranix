<?php
namespace Laranix\Auth\User;

use Carbon\Carbon;

interface LastLoginUpdater
{
    /**
     * Get the property name
     *
     * @return string
     */
    public function getLastLoginKey() : string;

    /**
     * Get last login
     *
     * @return \Carbon\Carbon
     */
    public function getLastLogin() : Carbon;

    /**
     * Update a users last login
     *
     * @param \Carbon\Carbon|null $override
     * @return $this|\Carbon\Carbon
     */
    public function updateLastLogin(?Carbon $override = null);
}
