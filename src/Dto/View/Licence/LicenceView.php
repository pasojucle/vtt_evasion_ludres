<?php

declare(strict_types=1);

namespace App\Dto\View\Licence;

use App\Dto\View\BadgeView;
use App\Dto\View\Interface\TurboStreamViewInterface;
use App\Dto\View\LinkView;

readonly class LicenceView implements TurboStreamViewInterface
{
    public function __construct(
        public int $id,
        public int $licenceId,
        public string $number,
        public BadgeView $season,
        public string $createdAt,
        public String $state,
        public string $category,
        public ?BadgeView $yearlyCoverage,
        public string $coverage,
        public string $bikeType,
        public ?string $familyMember,
        public ?string $familyMemberUrl,
        public LinkView $sendNuberLicenceAction,
        public LinkView $editAction,
    ) {
    }
}
