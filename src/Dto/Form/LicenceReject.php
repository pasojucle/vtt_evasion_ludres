<?php

declare(strict_types=1);

namespace App\Dto\Form;

use App\Entity\Licence;

class LicenceReject
{
    public function __construct(
        public Licence $licence,
        public string $content,
    ) {
    }
}
