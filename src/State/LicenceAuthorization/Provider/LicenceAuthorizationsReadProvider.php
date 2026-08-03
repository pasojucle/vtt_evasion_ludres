<?php

declare(strict_types=1);

namespace App\State\LicenceAuthorization\Provider;


use App\Dto\View\LicenceAuthorization\LicenceAuthorizationsSheetView;
use App\Dto\View\LicenceAuthorization\LicenceAuthorizationsView;
use App\Entity\Licence;

use App\Mapper\LicenceAuthorization\LicenceAuthorizationsReadMapper;
use App\State\Interface\TurboStreamProviderInterface;


/**
 * @implements TurboStreamProviderInterface<Licence>
 */
class LicenceAuthorizationsReadProvider implements TurboStreamProviderInterface
{
    public function __construct(
        private LicenceAuthorizationsReadMapper $licenceAuthorizationsMapper,
    ) {
    }

    public function getFormView(object $entity, ?string $fallback = null): LicenceAuthorizationsSheetView
    {
        return new LicenceAuthorizationsSheetView(
            title: 'Modifier',
            description: 'Modifier les autorisations',
            action: 'Modifier'
        );
        
    }

    public function getFormOptions(object $entity): array
    {
        return [
            'attr' => [
                'data-controller' => 'form-modifier',
            ],
        ];
    }

    public function getStreamView(object $entity): LicenceAuthorizationsView
    {

        return $this->licenceAuthorizationsMapper->mapToView($entity);
    }
}
