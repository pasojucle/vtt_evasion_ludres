<?php

namespace App\ViewModel;

use ReflectionClass;

class AbstractViewModel 
{
    public function __construct()
    {
        $reflectionClass = new ReflectionClass(self::class);
        $properties = $reflectionClass->getProperties();
        if (!empty($properties)) {
            foreach($properties as $property) {
                $propertyName = $property->getName();
                $this->$propertyName = null;
            }
        }
    }
}