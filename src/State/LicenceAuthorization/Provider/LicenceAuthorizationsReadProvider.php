<?php

declare(strict_types=1);

namespace App\State\LicenceAuthorization\Provider;

use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Contract\Provider\TurboStreamProviderInterface;
use App\Core\Dto\FlashMessage;
use App\Core\Dto\HandlerContext;
use App\Dto\View\LicenceAuthorization\LicenceAuthorizationsSheetView;
use App\Dto\View\LicenceAuthorization\LicenceAuthorizationsView;
use App\Entity\Licence;

use App\Mapper\LicenceAuthorization\LicenceAuthorizationsReadMapper;

/**
 * @implements TurboStreamProviderInterface<Licence>
 */
class LicenceAuthorizationsReadProvider implements FormComponentProviderInterface, TurboStreamProviderInterface
{
    public function __construct(
        private LicenceAuthorizationsReadMapper $licenceAuthorizationsMapper,
    ) {
    }

    public function getView(object $data, ?HandlerContext $context = null): LicenceAuthorizationsSheetView
    {
        return new LicenceAuthorizationsSheetView(
            title: 'Modifier',
            description: 'Modifier les autorisations',
            action: 'Modifier'
        );
    }

    public function getFormOptions(object $data): array
    {
        return [
            'attr' => [
                'data-controller' => 'form-modifier',
                'data-action' => 'turbo:submit-end->sheet#handleFormSubmit',
            ],
        ];
    }

    public function getStreamView(object $entity, ?FlashMessage $flashMessage = null, ?HandlerContext $context = null): LicenceAuthorizationsView
    {
        return $this->licenceAuthorizationsMapper->mapToView($entity);
    }
}
