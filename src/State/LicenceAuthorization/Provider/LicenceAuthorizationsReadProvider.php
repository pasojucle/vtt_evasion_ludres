<?php

declare(strict_types=1);

namespace App\State\LicenceAuthorization\Provider;

use App\Dto\State\TurboStreamContext;
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
                'data-action'=> 'turbo:submit-end->sheet#handleFormSubmit',
            ],
        ];
    }

    public function getStreamView(object $entity, ?TurboStreamContext $context = null): LicenceAuthorizationsView
    {
        return $this->licenceAuthorizationsMapper->mapToView($entity);
    }
}
