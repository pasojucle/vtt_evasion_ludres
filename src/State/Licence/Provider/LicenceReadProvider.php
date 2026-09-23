<?php

declare(strict_types=1);

namespace App\State\Licence\Provider;

use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Contract\Provider\TurboStreamProviderInterface;
use App\Core\Dto\FlashMessage;
use App\Core\Dto\HandlerContext;
use App\Dto\View\Licence\LicenceView;
use App\Dto\View\SheetView;
use App\Entity\User;
use App\Mapper\Licence\LicenceReadMapper;

/**
 * @implements FormComponentProviderInterface<User>
 */
class LicenceReadProvider implements FormComponentProviderInterface, TurboStreamProviderInterface
{
    public function __construct(
        private LicenceReadMapper $licenceReadMapper,
    ) {
    }

    public function getView(object $data, ?HandlerContext $context = null): SheetView
    {
        return new SheetView(
            title: 'Modifier',
            description: 'Modifier les informations de la licence',
            action: 'Modifier'
        );
    }

    public function getFormOptions(object $data): array
    {
        return [
            'attr' => [
                'data-controller' => 'form-validator',
                'data-action' => 'turbo:submit-end->sheet#handleFormSubmit',
            ],
        ];
    }

    public function getStreamView(object $data, ?FlashMessage $flashMessage = null, ?HandlerContext $context = null): LicenceView
    {
        return $this->licenceReadMapper->mapToView($data);
    }
}
