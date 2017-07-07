<?php
namespace Laranix\AppSettings;

use Illuminate\Support\Facades\Facade as BaseFacade;

class Facade extends BaseFacade
{
    protected static function getFacadeAccessor()
    {
        return AppSettings::class;
    }
}
