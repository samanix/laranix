<?php
namespace Laranix\Themer\Images;

use Illuminate\Support\Facades\Facade as BaseFacade;

class Facade extends BaseFacade
{
    protected static function getFacadeAccessor()
    {
        return Images::class;
    }
}
