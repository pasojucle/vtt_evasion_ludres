<?php

declare(strict_types=1);

namespace App\ViewModel;

use ReflectionClass;
use ReflectionProperty;

class AbstractViewModel
{
    public function __construct()
    {
        $reflectionClass = new ReflectionClass($this);
        $properties = $reflectionClass->getProperties();
        if (!empty($properties)) {
            foreach ($properties as $property) {
                if (ReflectionProperty::IS_PUBLIC === $property->getModifiers()) {
                    $propertyName = $property->getName();
                    $this->{$propertyName} = null;
                }
            }
        }
    }

    public function toString(): string
    {
        $reflectionClass = new ReflectionClass($this);

        $properties = $reflectionClass->getProperties();
        $entity = [];
        if (!empty($properties)) {
            foreach ($properties as $property) {
                if ('id' !== $property->getName()) {
                    $propertyName = $property->getName();
                    $entity[] = $this->{$propertyName};
                }
            }
        }

        return implode(', ', $entity);
    }
}
