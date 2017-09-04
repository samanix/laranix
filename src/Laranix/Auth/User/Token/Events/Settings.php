<?php
namespace Laranix\Auth\User\Token\Events;

class Settings
{
    const TYPEID_CREATED = 1;
    const TYPEID_UPDATED = 2;
    const TYPEID_VERIFY_ATTEMPT = 4;
    const TYPEID_FAILED = 8;
    const TYPEID_COMPLETED = 16;
    const TYPEID_CREATE_UPDATE_ATTEMPT = 32;
}
