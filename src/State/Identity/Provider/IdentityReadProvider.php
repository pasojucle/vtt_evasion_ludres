<?php

declare(strict_types=1);

namespace App\State\Identity\Provider;

use App\Dto\View\Identity\IdentityView;
use App\Dto\View\SheetView;
use App\Entity\Identity;
use App\Mapper\Identity\IdentityReadMapper;
use App\State\Interface\FormComponentProviderInterface;
use App\State\Interface\TurboStreamProviderInterface;


/**
 * @implements FormComponentProviderInterface<Identity>
 * @implements TurboStreamProviderInterface<Identity>
 */
class IdentityReadProvider implements FormComponentProviderInterface, TurboStreamProviderInterface
{
    public function __construct(
        private IdentityReadMapper $identityReadMapper,
    ) {
    }

    public function getFormView(object $entity, ?string $fallback = null): SheetView
    {
        return new SheetView(
            title: 'Modifier',
            description: 'blabla',
            action: 'Modifier'
        );
        
    }

    public function getStreamView(object $entity): IdentityView
    {
        $address = $entity->getAddress();

        return $this->identityReadMapper->mapToView($entity, $address);
    }
}
