<?php
namespace Laranix\Auth\Events\Login;

class Settings
{
    const TYPEID_AUTHENTICATED = 1;
    const TYPEID_LOGIN = 2;
    const TYPEID_LOGIN_FAILED = 4;
    const TYPEID_LOGIN_LOCKOUT = 8;
    const TYPEID_LOGIN_RESTRICTED = 16;
}
