<?php
namespace Laranix\Auth\User\Token\Api;

interface ApiTokenProvider
{
    /**
     * Get name of api token
     *
     * @return string
     */
    public function getApiTokenName() : string;

    /**
     * Get the api token
     *
     * @return string
     */
    public function getApiToken() : ?string;
}
