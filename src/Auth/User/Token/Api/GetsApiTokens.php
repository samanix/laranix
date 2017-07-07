<?php
namespace Laranix\Auth\User\Token\Api;

trait GetsApiTokens
{
    /**
     * Get name of api token
     *
     * @return string
     */
    public function getApiTokenName() : string
    {
        return 'api_token';
    }

    /**
     * Get the api token
     *
     * @return string|null
     */
    public function getApiToken() : ?string
    {
        return $this->getAttributeFromArray($this->getApiTokenName());
    }
}
