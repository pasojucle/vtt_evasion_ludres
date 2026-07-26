<?php

declare(strict_types=1);

namespace App\Dto\View\EmergencyContact;

use App\Dto\View\Interface\TurboStreamViewInterface;
use App\Dto\View\LinkView;
use App\Dto\View\PhoneView;


readonly class EmergencyContactView implements TurboStreamViewInterface
{
    public function __construct(
        public int $id,
        public string $kinship,
        public PhoneView $phone,
        public LinkView $action,
    ) {
    }
}
