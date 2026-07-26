<?php

declare(strict_types=1);

namespace App\State\EmergencyContact\Provider;

use App\Dto\View\EmergencyContact\EmergencyContactView;
use App\Dto\View\SheetView;
use App\Entity\EmergencyContact;
use App\Mapper\EmergencyContact\EmergencyContactReadMapper;
use App\State\Interface\TurboStreamProviderInterface;


/**
 * @implements TurboStreamProviderInterface<EmergencyContact>
 */
class EmergencyContactReadProvider implements TurboStreamProviderInterface
{
    public function __construct(
        private EmergencyContactReadMapper $emergencyContactReadMapper,
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

    public function getFormOptions(object $entity): array
    {   

        return [
            'attr' => [
                'data-controller' => 'form-validator',
            ],
        ];
    }

    public function getStreamView(object $entity): EmergencyContactView
    {

        return $this->emergencyContactReadMapper->mapToView($entity);
    }
}
