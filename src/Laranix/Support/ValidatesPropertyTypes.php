<?php
namespace Laranix\Support;

use Laranix\Support\Exception\InvalidTypeException;
use Laranix\Support\IO\Str\Str;

trait ValidatesPropertyTypes
{
    /**
     * Check property is valid against type
     *
     * @param string $property
     * @param string $type
     * @return bool
     */
    protected function validatePropertyType(string $property, string $type) : bool
    {
        switch ($type) {
            case 'any':
            case 'notnull':
            case 'isset':
            case 'set':
                return true;
            case 'string':
                return is_string($this->{$property});
            case 'email':
                return filter_var($this->{$property}, FILTER_VALIDATE_EMAIL) !== false;
            case 'url':
                return filter_var($this->{$property}, FILTER_VALIDATE_URL) !== false;
            case 'int':
                return is_int($this->{$property});
            case 'bool':
            case 'boolean':
                return is_bool($this->{$property});
            case 'array':
                return is_array($this->{$property});
            case 'null':
                return $this->{$property} === null;
            case 'is':
            case 'instanceof':
            default:
                return $this->{$property} instanceof $type;
        }
    }

    /**
     * Throw exception when invalid type detected
     *
     * @param string $property
     * @param array  $types
     * @param bool   $optional
     * @param string $exception
     * @throws \Laranix\Support\Exception\InvalidTypeException
     */
    protected function throwInvalidTypeException(
        string $property, array $types, bool $optional = false, string $exception = InvalidTypeException::class
    ) {
        $str = "Expected '{{types}}' for {{optional}} property '{{property}}' in {{class}}, got '{{actualtype}}'";

        throw new $exception(Str::format($str, [
            'types'     => implode('|', array_keys($types)),
            'optional'  => $optional ? 'optional' : null,
            'property'  => $property,
            'class'     => get_class($this),
            'actualtype'=> gettype($this->{$property}),
        ]));
    }
}
