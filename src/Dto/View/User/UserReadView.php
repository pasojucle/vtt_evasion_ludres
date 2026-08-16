<?php

declare(strict_types=1);

namespace App\Dto\View\User;

use App\Dto\View\BadgeView;
use App\Dto\View\TabEntityInterface;
use Doctrine\Common\Collections\Collection;

readonly class UserReadView implements TabEntityInterface
{
    /**
     * @param BadgeView[] $authorizations
     */
    public function __construct(
        public int $id,
        public int $identityId,
        public int $licenceId,
        public ?int $healthId,
        public string $passportPhoto,
        public string $fullName,
        public ?string $levelType,
        public BadgeView $level,
        public BadgeView $season,
        public array $authorizations,
        public Collection $gardians,
        public ?int $emergencyContactId,
        public array $participationParams,
    ) {
    }
}
