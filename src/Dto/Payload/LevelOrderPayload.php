<?php

declare(strict_types=1);

namespace App\Dto\Payload;

use App\Entity\Level;
use Symfony\Component\HttpFoundation\Request;

class LevelOrderPayload
{
    public function __construct(
        public Level $level,
        public int $newOrder
    ) {
    }

    public static function fromRequest(Level $level, Request $request): self
    {
        return new self(
            $level,
            newOrder: $request->request->getInt('newOrder'),
        );
    }
}
