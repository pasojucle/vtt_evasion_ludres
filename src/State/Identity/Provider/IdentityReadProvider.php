<?php

declare(strict_types=1);

namespace App\State\Identity\Provider;

use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Contract\Provider\TurboStreamProviderInterface;
use App\Core\Dto\FlashMessage;
use App\Core\Dto\HandlerContext;
use App\Dto\View\Identity\IdentitySheetView;
use App\Dto\View\Identity\IdentityView;
use App\Entity\Identity;
use App\Mapper\Identity\IdentityReadMapper;

/**
 * @implements TurboStreamProviderInterface<Identity>
 */
class IdentityReadProvider implements FormComponentProviderInterface, TurboStreamProviderInterface
{
    public function __construct(
        private IdentityReadMapper $identityReadMapper,
    ) {
    }

    public function getView(object $data, ?HandlerContext $context = null): IdentitySheetView
    {
        return new IdentitySheetView(
            title: 'Modifier',
            description: 'Modifier l\'identité',
            action: 'Modifier'
        );
    }

    public function getFormOptions(object $data): array
    {
        $user = $data->getMember();
        $licence = $user->getLastLicence();
        return [
            'category' => $licence->getCategory(),
            'is_yearly' => $licence->getState()->isYearly(),
            'is_gardian' => false,
            'attr' => [
                'data-controller' => 'form-modifier form-validator',
                'data-action' => 'turbo:submit-end->sheet#handleFormSubmit',
            ],
        ];
    }

    public function getStreamView(object $entity, ?FlashMessage $flashMessage = null, ?HandlerContext $context = null): IdentityView
    {
        $address = $entity->getAddress();

        return $this->identityReadMapper->mapToView($entity, $address);
    }
}
