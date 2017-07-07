<?php
namespace Laranix\Themer\Script;

use Illuminate\Support\Facades\Facade as BaseFacade;

class Facade extends BaseFacade
{
    protected static function getFacadeAccessor()
    {
        return Script::class;
    }
}
