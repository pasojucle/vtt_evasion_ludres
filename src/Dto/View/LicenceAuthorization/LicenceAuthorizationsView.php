<?php

declare(strict_types=1);

namespace App\Dto\View\LicenceAuthorization;

use App\Dto\View\Interface\TurboStreamViewInterface;
use App\Dto\View\LinkView;

readonly class LicenceAuthorizationsView implements TurboStreamViewInterface
{
    /**
     * @param LicenceAuthorizationView[] $authorizations
     */
    public function __construct(
        public int $licenceId,
        public array $authorizations,
        public LinkView $editAction,
    ) {
    }
}
