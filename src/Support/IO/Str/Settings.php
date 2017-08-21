<?php
namespace Laranix\Support\IO\Str;

use Laranix\Support\Settings as BaseSettings;

class Settings extends BaseSettings
{
    /**
     * Names of required properties
     *
     * @var array|string
     */
    protected $required = [
        'removeUnparsed'        => 'bool',
        'unparsedReplacement'   => 'string',
        'leftSeparator'         => 'string',
        'rightSeparator'        => 'string',
        'removeExtraSpaces'     => 'bool',
    ];

    /**
     * If true, will remove unparsed keys from string.
     *
     * @var bool
     */
    public $removeUnparsed = false;

    /**
     * Replacement for unparsed keys.
     *
     * @var string
     */
    public $unparsedReplacement = '';

    /**
     * Left delimiter for paramaters.
     *
     * @var string
     */
    public $leftSeparator = '{{';

    /**
     * Right delimiter for paramaters.
     *
     * @var string
     */
    public $rightSeparator = '}}';

    /**
     * Remove double or extra spaces in string
     *
     * @var bool
     */
    public $removeExtraSpaces = true;
}
