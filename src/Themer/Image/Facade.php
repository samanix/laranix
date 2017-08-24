<?php
namespace Laranix\Themer\Image;

use Illuminate\Support\Facades\Facade as BaseFacade;

class Facade extends BaseFacade
{
    protected static function getFacadeAccessor()
    {
        return Images::class;
    }
}
