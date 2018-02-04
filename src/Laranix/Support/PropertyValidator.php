<?php
namespace Laranix\Support;

interface PropertyValidator
{
    /**
     * @param array $properties
     * @return bool
     */
    public function validateProperties(array $properties): bool;

    /**
     * @param string       $property
     * @param string|array $allowed
     * @return bool
     */
    public function validateProperty(string $property, $allowed): bool;
}
