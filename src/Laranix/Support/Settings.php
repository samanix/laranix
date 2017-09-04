<?php
namespace Laranix\Support;

use Laranix\Support\Exception\LaranixSettingsException;
use Laranix\Support\IO\Str\Str;

abstract class Settings
{
    /**
     * Required properties
     *
     * @var array
     */
    protected $required = [];

    /**
     * Required types (if not set in $this->required)
     *
     * @var array
     */
    protected $requiredTypes = [];

    /**
     * Ignored properties
     * Useful if all are set to required but you want
     * to exclude some
     *
     * @var array
     */
    protected $ignored = [];

    /**
     * Required properties and types parsed in to one array
     *
     * @var array
     */
    protected $requiredParsed;

    /**
     * If all properties are required
     *
     * @var bool
     */
    protected $allRequired = false;

    /**
     * Settings constructor.
     *
     * @param array $values
     */
    public function __construct(array $values = [])
    {
        $this->assignPropertyValues($values);

        $this->setRequiredProperties($this->required);
    }

    /**
     * Assign values to properties
     *
     * @param array $values
     */
    protected function assignPropertyValues(array $values)
    {
        foreach ($values as $property => $value) {
            if (property_exists($this, $property)) {
                $this->{$property} = $value;
            }
        }
    }

    /**
     * Check the settings have all required settings with valid types
     *
     * @param   bool $refresh
     * @return  bool
     */
    public function hasRequiredSettings(bool $refresh = false)
    {
        $required = $this->getParsedRequiredProperties($refresh);

        if (!$this->allRequired && (empty($required) || $required === null)) {
            return true;
        }

        foreach ($required as $property => $allowed) {
            $this->validateProperty($property, $allowed);
        }

        return true;
    }

    /**
     * Validate the property against its allowed types
     *
     * @param string $property
     * @param array  $allowed
     * @throws \Laranix\Support\Exception\LaranixSettingsException
     */
    protected function validateProperty(string $property, array $allowed)
    {
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
            if ($valid = $this->isValid($property, $type)) {
                break;
            }
        }

        if (!$valid) {
            $this->throwInvalidException($property, $types, $optional);
        }
    }

    /**
     * Check property is valid against type
     *
     * @param string $property
     * @param string $type
     * @return bool
     */
    protected function isValid(string $property, string $type) : bool
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
     * @throws \Laranix\Support\Exception\LaranixSettingsException
     */
    protected function throwInvalidException(string $property, array $types, bool $optional = false)
    {
        $str = "Expected '{{types}}' for {{optional}} property '{{property}}' in {{class}}, got '{{actualtype}}'";

        throw new LaranixSettingsException(Str::format($str, [
            'types'     => implode('|', array_keys($types)),
            'optional'  => $optional ? 'optional' : null,
            'property'  => $property,
            'class'     => get_class($this),
            'actualtype'=> gettype($this->{$property}),
        ]));
    }

    /**
     * Parse required properties + types
     *
     * @param   bool    $refresh
     * @return  array
     */
    protected function parseRequiredProperties(bool $refresh = false)
    {
        if ($this->requiredParsed !== null && !$refresh) {
            return $this->requiredParsed;
        }

        $ignored = array_flip($this->getIgnoredProperties());

        foreach ($this->required as $index => $item) {
            $property = is_int($index) ? $item : $index;
            $type = 'any';

            if (isset($ignored[$property])) {
                continue;
            }

            if (isset($this->requiredTypes[$property])) {
                $type = $this->requiredTypes[$property];
            } elseif (!is_int($index) && $item !== '*' && !$this->allRequired) {
                $type = $item;
            }

            $this->requiredParsed[$property] = $this->parseAllowedTypes($type);
        }

        return $this->requiredParsed;
    }

    /**
     * Get allowed types
     *
     * @param $types
     * @return array
     */
    protected function parseAllowedTypes($types) : array
    {
        return is_array($types) ? $types : explode('|', $types);
    }

    /**
     * Get required properties
     *
     * @return array
     */
    public function getRequiredProperties() : array
    {
        return $this->required ?? [];
    }

    /**
     * Set required properties
     *
     * @param array $required
     * @return $this
     */
    public function setRequiredProperties(array $required)
    {
        if (empty($required)) {
            return $this;
        }

        if (isset($required[0]) && $required[0] === '*') {
            $this->required = get_object_vars($this);
            $this->allRequired = true;
        } else {
            $this->required = $required;
        }

        unset($this->required['required'],
              $this->required['requiredTypes'],
              $this->required['ignored'],
              $this->required['requiredParsed'],
              $this->required['allRequired']);

        return $this;
    }

    /**
     * Get ignored properties
     *
     * @return array
     */
    public function getIgnoredProperties() : array
    {
        return $this->ignored ?? [];
    }

    /**
     * Set ignored properties
     *
     * @param array $ignored
     * @return $this
     */
    public function setIgnoredProperties(array $ignored)
    {
        $this->ignored = $ignored;

        return $this;
    }

    /**
     * Get required properties and their type
     *
     * @param   bool $refresh
     * @return  array
     */
    public function getParsedRequiredProperties(bool $refresh = false) : ?array
    {
        return $this->parseRequiredProperties($refresh);
    }

    /**
     * Set required types
     *
     * @param array $types
     * @return $this
     */
    public function setRequiredTypes(array $types)
    {
        $this->required = array_merge($this->required, $types);
        $this->requiredTypes = $types;

        return $this;
    }

    /**
     * Set required types for property
     *
     * @param string $property
     * @param        $types
     * @return $this
     */
    public function setRequiredPropertyTypes(string $property, $types)
    {
        $this->required[$property] = $types;
        $this->requiredTypes[$property] = $types;

        return $this;
    }

    /**
     * Get required property types
     *
     * @param string $property
     * @return array|null
     */
    public function getRequiredPropertyTypes(string $property) : ?array
    {
        return $this->getParsedRequiredProperties()[$property] ?? null;
    }
}