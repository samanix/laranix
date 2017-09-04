<?php
namespace Laranix\AntiSpam\Recaptcha;

use Illuminate\Support\Facades\Facade as BaseFacade;

class Facade extends BaseFacade
{
    protected static function getFacadeAccessor()
    {
        return Recaptcha::class;
    }
}
