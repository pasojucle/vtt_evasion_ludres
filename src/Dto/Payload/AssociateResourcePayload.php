<?php

declare(strict_types=1);

namespace App\Dto\Payload;

use App\Core\Contract\PayloadInterface;

class AssociateResourcePayload implements PayloadInterface
{
    public function __construct(
        public object $parent,
        public object $data
    ) {
    }
}
