<?php
namespace Laranix\Support;

use Laranix\Support\Exception\InvalidTypeException;
use Laranix\Support\IO\Str\Str;

trait ValidatesRequiredProperties
{
    /**
     * Validate the property against its allowed types
     *
     * @param string        $property
     * @param string|array  $allowed
     * @throws \Laranix\Support\Exception\InvalidTypeException
     */
    protected function validateProperty(string $property, $allowed)
    {
        if (is_string($allowed)) {
            $allowed = explode('|', $allowed);
        }

        if (!is_array($allowed)) {
            throw new InvalidTypeException('$allowed must be a string or array');
        }

        $types = array_flip($allowed);

        $optional = false;
        $valid = false;

        if (isset($types['optional'])) {
            if ($this->{$property} === null) {
                return;
            }

            unset($types['optional']);

            $optional = true;
        }

        foreach ($allowed as $index => $type) {
            if ($valid = $this->validatePropertyType($property, $type)) {
                break;
            }
        }

        if (!$valid) {
            $this->throwInvalidTypeException($property, $types, $optional);
        }
    }

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
     * @throws \Laranix\Support\Exception\InvalidTypeException
     */
    protected function throwInvalidTypeException(string $property, array $types, bool $optional = false)
    {
        $str = "Expected '{{types}}' for {{optional}} property '{{property}}' in {{class}}, got '{{actualtype}}'";

        throw new InvalidTypeException(Str::format($str, [
            'types'     => implode('|', array_keys($types)),
            'optional'  => $optional ? 'optional' : null,
            'property'  => $property,
            'class'     => get_class($this),
            'actualtype'=> gettype($this->{$property}),
        ]));
    }
}
