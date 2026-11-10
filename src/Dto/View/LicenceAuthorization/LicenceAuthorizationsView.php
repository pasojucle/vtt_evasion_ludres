<?php

declare(strict_types=1);

namespace App\Dto\View\LicenceAuthorization;

use App\Core\Contract\View\ComponentViewInterface;
use App\Core\Contract\View\TurboStreamViewInterface;
use App\Dto\View\LinkView;

readonly class LicenceAuthorizationsView implements TurboStreamViewInterface, ComponentViewInterface
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

    public function getStreamTemplate(): string
    {
        return 'licence_authorization/admin/update.lazy.html.twig';
    }
    
    public function getName(): string
    {
        return 'view';
    }

    public function getTemplate(): string
    {
        return 'licence_authorization/admin/show.html.twig';
    }
}
