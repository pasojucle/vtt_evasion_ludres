<?php

declare(strict_types=1);

namespace App\Dto\Payload;

use Symfony\Component\HttpFoundation\Request;

class SortablePayload
{
    public function __construct(
        public object $entity,
        public int $newOrder
    ) {
    }

    public static function fromRequest(object $entity, Request $request): self
    {
        return new self(
            $entity,
            newOrder: $request->request->getInt('newOrder'),
        );
    }
}
