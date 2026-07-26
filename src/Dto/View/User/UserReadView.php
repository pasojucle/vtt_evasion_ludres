<?php

declare(strict_types=1);

namespace App\Dto\View\User;

use App\Dto\View\BadgeView;
use App\Dto\View\Gardian\EmergencyContatctView;
use App\Dto\View\Gardian\GardianView;
use App\Dto\View\TabEntityInterface;
use Doctrine\Common\Collections\ArrayCollection;

readonly class UserReadView implements TabEntityInterface
{
    /**
     * @param BadgeView[] $authorizations
     * @param GardianView[] $gardians
     */
    public function __construct(
        public int $id,
        public int $identityId,
        public int $licenceId,
        public string $passportPhoto,
        public string $fullName,
        public string $levelType,
        public BadgeView $level,
        public BadgeView $season,
        public array $authorizations,
        public ArrayCollection $gardians,
        public ?int $emergencyContactId,
    ) {
    }
}
