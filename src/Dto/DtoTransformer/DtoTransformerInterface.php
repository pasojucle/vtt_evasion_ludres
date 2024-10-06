<?php

declare (strict_types=1);

namespace App\Dto\DtoTransformer;

interface DtoTransformerInterface
{
    public function fromEntity(?object $entity);

    public function fromEntities(array $entities);
}