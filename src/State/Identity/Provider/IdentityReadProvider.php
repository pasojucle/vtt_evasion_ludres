<?php

declare(strict_types=1);

namespace App\State\Identity\Provider;

use App\Dto\State\TurboStreamContext;
use App\Dto\View\Identity\IdentitySheetView;
use App\Dto\View\Identity\IdentityView;
use App\Entity\Identity;
use App\Mapper\Identity\IdentityReadMapper;

use App\State\Interface\TurboStreamProviderInterface;

/**
 * @implements TurboStreamProviderInterface<Identity>
 */
class IdentityReadProvider implements TurboStreamProviderInterface
{
    public function __construct(
        private IdentityReadMapper $identityReadMapper,
    ) {
    }

    public function getFormView(object $entity, ?string $fallback = null): IdentitySheetView
    {
        return new IdentitySheetView(
            title: 'Modifier',
            description: 'Modifier l\'identité',
            action: 'Modifier'
        );
    }

    public function getFormOptions(object $entity): array
    {
        $user = $entity->getMember();
        $licence = $user->getLastLicence();
        return [
            'category' => $licence->getCategory(),
            'is_yearly' => $licence->getState()->isYearly(),
            'is_gardian' => false,
            'attr' => [
                'data-controller' => 'form-modifier form-validator',
                'data-action'=> 'turbo:submit-end->sheet#handleFormSubmit',
            ],
        ];
    }

    public function getStreamView(object $entity, ?TurboStreamContext $context = null): IdentityView
    {
        $address = $entity->getAddress();

        return $this->identityReadMapper->mapToView($entity, $address);
    }
}
