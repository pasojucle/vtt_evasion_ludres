<?php

declare(strict_types=1);

namespace App\Dto\Payload;

use App\Entity\Cluster;
use App\Entity\Session;

class SessionDelete
{
    public function __construct(
        public Session $session,
        public Cluster $cluster,
    ) {
    }
}
