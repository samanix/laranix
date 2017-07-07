<?php
namespace Laranix\AntiSpam\Sequence;

use Illuminate\Support\Facades\Facade as BaseFacade;

class Facade extends BaseFacade
{
    protected static function getFacadeAccessor()
    {
        return Sequence::class;
    }
}
