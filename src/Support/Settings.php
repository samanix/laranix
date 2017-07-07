<?php
namespace Laranix\Support;

use Laranix\Support\Exception\LaranixSettingsException;

abstract class Settings
{
//    /** TODO
//     * Set callbacks, fired when property is set
//     *
//     * @var array
//     */
//    //protected $setCallbacks = [];

//    /** TODO
//     * Allowed types when parameter not required
//     *
//     * @var array
//     */
//    protected $allowedTypes = [];

    /**
     * Names of required properties
     *
     * @var array|string
     */
    protected $required = [];

    /**
     * If using a large options file, you can ignore settings here
     *
     * @var array
     */
    protected $requiredExcept = [];

    /**
     * If all is specified, you can set types by property name here
     *
     * @var array
     */
    protected $requiredTypes = [];

    /**
     * @var bool
     */
    protected $allRequired = false;

    /**
     * SettingsBase constructor.
     *
     * @param array $values
     */
    public function __construct(array $values = [])
    {
        $this->assignPropertyValues($values);

        $this->setRequired($this->required);
    }

    /**
     * Assign attributes to properties
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
     * Check for required parameters
     *
     * @return bool
     * @throws \Laranix\Support\Exception\LaranixSettingsException
     */
    public function hasRequired()
    {
        $required = $this->getRequired();

        if (empty($required) || $required === null) {
            return true;
        }

        $ignore = array_flip($this->getRequiredExcept());

        foreach ($required as $index => $type) {
            $property = is_int($index) ? $type : $index;

            if (isset($ignore[$property])) {
                continue;
            }

            $allowed = is_int($index) || $this->allRequired ? null : $type;

            $this->validateProperty($property, $allowed);
        }

        return true;
    }

    /**
     * Check property is valid
     *
     * @param string      $property
     * @param string|null $allowedTypes
     * @throws \Laranix\Support\Exception\LaranixSettingsException
     */
    protected function validateProperty(string $property, ?string $allowedTypes = null)
    {
        $type = $this->getPropertyType($property, $allowedTypes);

        if ($type === null || $type === 'null') {
            if(!is_null($this->{$property})) {
                throw new LaranixSettingsException(sprintf("'%s' is required but not set in %s", $property, get_class($this)));
            }

            return;
        }

        $valid = false;

        if (is_array($type)) {
            foreach ($type as $value) {
                if ($valid = $this->isValid($property, $value)) {
                    break;
                }
            }
        } else {
            $valid = $this->isValid($property, $type);
        }

        if (!$valid) {
            $types = is_array($type) ? implode('|', $type) : $type;

            throw new LaranixSettingsException(sprintf("Expected '%s' for '%s' in %s, got %s",
                                                       $types, $property, get_class($this), gettype($this->$property)));
        }
    }

    /**
     * Check value is valid
     *
     * @param string $property
     * @param string $type
     * @return bool
     */
    protected function isValid(string $property, string $type) : bool
    {
        switch ($type) {
            case 'string':
                return is_string($this->$property);
            case 'email':
                return filter_var($this->$property, FILTER_VALIDATE_EMAIL) !== false;
            case 'url':
                return filter_var($this->$property, FILTER_VALIDATE_URL) !== false;
            case 'int':
                return is_int($this->$property);
            case 'bool':
            case 'boolean':
                return is_bool($this->$property);
            case 'array':
                return is_array($this->$property);
            case 'null':
                return $this->$property === null;
            case 'is':
            case 'instanceof':
            default:
                return $this->$property instanceof $type;
        }
    }

    /**
     * Get expected property type(s)
     *
     * @param string      $property
     * @param string|null $type
     * @return array|null|string
     */
    protected function getPropertyType(string $property, string $type = null)
    {
        $type = $type ?? $this->getRequiredType($property);

        return strpos($type, '|') !== false ? explode('|', $type) : $type;
    }

    /**
     * Set required
     *
     * @param array|string $required
     * @return $this
     */
    public function setRequired($required)
    {
        if (empty($required)) {
            return $this;
        }

        if ($required === 'all' || (isset($required[0]) && $required[0] === '*')) {
            $this->allRequired = true;
            $required = get_object_vars($this);
        } elseif (!is_array($required)) {
            $required = [$required];
        }

        unset($required['required'], $required['requiredTypes'], $required['requiredExcept'], $required['allRequired']);

        $this->required = $required;

        return $this;
    }

    /**
     * Get required
     *
     * @return array
     */
    public function getRequired() : array
    {
        return $this->required ?? [];
    }

    /**
     * Set requirement exceptions
     *
     * @param array $requiredExcept
     * @return $this
     */
    public function setRequiredExcept(array $requiredExcept)
    {
        $this->requiredExcept = $requiredExcept;

        return $this;
    }

    /**
     * Get requirement exceptions
     * @return array
     */
    public function getRequiredExcept() : array
    {
        return $this->requiredExcept ?? [];
    }

    /**
     * Set required types
     *
     * @param array $requiredTypes
     * @return $this
     */
    public function setRequiredTypes(array $requiredTypes)
    {
        $this->requiredTypes = $requiredTypes;

        return $this;
    }

    /**
     * Get required types
     *
     * @return array|null
     */
    public function getRequiredTypes() : array
    {
        return $this->requiredTypes ?? [];
    }

    /**
     * Get required type for property
     *
     * @param string $property
     * @return null|string
     */
    public function getRequiredType(string $property) : ?string
    {
        return $this->requiredTypes[$property] ?? null;
    }
}
