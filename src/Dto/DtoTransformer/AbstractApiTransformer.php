<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use ReflectionClass;
use ReflectionProperty;

abstract class AbstractApiTransformer
{
    public function toArray(): array
    {
        $reflectionClass = new ReflectionClass(static::class);
        $properties = [];
        /** @var ReflectionProperty $property */
        foreach ($reflectionClass->getProperties() as $property) {
            $propertyName = $property->getName();
            $properties[$propertyName] = $this->$propertyName;
        }

        return $properties;
    }
}
