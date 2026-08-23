<?php

declare(strict_types=1);

namespace App\State\Licence\Provider;

use App\Dto\State\TurboStreamContext;
use App\Dto\View\Licence\LicenceView;
use App\Dto\View\SheetView;
use App\Entity\User;
use App\Mapper\Licence\LicenceReadMapper;
use App\State\Interface\TurboStreamProviderInterface;

/**
 * @implements TurboStreamProviderInterface<User>
 */
class LicenceReadProvider implements TurboStreamProviderInterface
{
    public function __construct(
        private LicenceReadMapper $licenceReadMapper,
    ) {
    }

    public function getFormView(object $entity, ?string $fallback = null): SheetView
    {
        return new SheetView(
            title: 'Modifier',
            description: 'Modifier les informations de la licence',
            action: 'Modifier'
        );
    }

    public function getFormOptions(object $entity): array
    {
        return [
            'attr' => [
                'data-controller' => 'form-validator',
            ],
        ];
    }

    public function getStreamView(object $entity, ?TurboStreamContext $context = null): LicenceView
    {
        return $this->licenceReadMapper->mapToView($entity);
    }
}
