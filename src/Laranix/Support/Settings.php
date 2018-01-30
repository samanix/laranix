<?php
namespace Laranix\Support;

use Laranix\Support\Exception\InvalidTypeException;

abstract class Settings
{
    use ValidatesRequiredProperties;

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
     * @throws \Laranix\Support\Exception\InvalidTypeException
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
