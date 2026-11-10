<?php

declare(strict_types=1);

namespace App\Dto\View\EmergencyContact;

use App\Core\Contract\View\ComponentViewInterface;
use App\Core\Contract\View\TurboStreamViewInterface;
use App\Dto\View\LinkView;
use App\Dto\View\PhoneView;

readonly class EmergencyContactView implements TurboStreamViewInterface, ComponentViewInterface
{
    public function __construct(
        public int $id,
        public ?string $kinship,
        public PhoneView $phone,
        public LinkView $action,
    ) {
    }

    public function getStreamTemplate(): string
    {
        return 'emergency_contact/admin/update.lazy.html.twig';
    }

    public function getName(): string
    {
        return 'view';
    }

    public function getTemplate(): string
    {
        return 'emergency_contact/admin/show.html.twig';
    }
}
