<?php

declare(strict_types=1);

namespace App\Service;

use ReflectionClass;

Class CsrfTokenService
{
    public function getTokenId(object $entity): string
    {
        $reflexion = new ReflectionClass($entity);

        return sprintf('toggle_%s_%s', strtolower($reflexion->getShortName()), $entity->getId());
    }
}