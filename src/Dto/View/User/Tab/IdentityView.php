<?php

declare(strict_types=1);

namespace App\Dto\View\User\Tab;

use App\Dto\View\Interface\TabContentInterface;
use Doctrine\Common\Collections\Collection;

readonly class IdentityView implements TabContentInterface
{
    public function __construct(
        public int $id,
        public int $identityId,
        public string $passportPhoto,
        public string $fullName,
        public Collection $gardians,
        public ?int $emergencyContactId,
    ) {
    }

    public function getName(): string
    {
        return 'content';
    }

    public function getTemplate(): string
    {
        return 'user/admin/show/tab_identity.html.twig';
    }
}
