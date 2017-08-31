<?php
namespace Laranix\Themer\Scripts;

use Illuminate\Support\Facades\Facade as BaseFacade;

class Facade extends BaseFacade
{
    protected static function getFacadeAccessor()
    {
        return Scripts::class;
    }
}
