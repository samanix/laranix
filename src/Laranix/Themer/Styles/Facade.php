<?php
namespace Laranix\Themer\Styles;

use Illuminate\Support\Facades\Facade as BaseFacade;

class Facade extends BaseFacade
{
    protected static function getFacadeAccessor()
    {
        return Styles::class;
    }
}
