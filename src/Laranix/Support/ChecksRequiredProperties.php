<?php
namespace Laranix\Support;

trait ChecksRequiredProperties
{
    use ValidatesPropertyTypes;

    /**
     * @param array $properties
     * @throws \Laranix\Support\Exception\InvalidTypeException
     */
    protected function hasRequiredProperties(array $properties)
    {
        foreach ($properties as $property => $types) {
            $valid = false;
            $allowed = explode('|', $types);

            if (property_exists($this, $property)) {
                foreach ($allowed as $type) {
                    if ($valid = $this->validatePropertyType($property, $type)) {
                        break;
                    }
                }
            }

            if (!$valid) {
                $this->throwInvalidTypeException($property, $allowed);
            }
        }
    }
}
