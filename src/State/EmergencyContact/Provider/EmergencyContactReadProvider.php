<?php

declare(strict_types=1);

namespace App\State\EmergencyContact\Provider;

use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Contract\Provider\TurboStreamProviderInterface;
use App\Core\Dto\FlashMessage;
use App\Core\Dto\HandlerContext;
use App\Dto\View\EmergencyContact\EmergencyContactView;
use App\Dto\View\SheetView;
use App\Entity\EmergencyContact;
use App\Mapper\EmergencyContact\EmergencyContactReadMapper;

/**
 * @implements FormComponentProviderInterface<EmergencyContact>
 */
class EmergencyContactReadProvider implements FormComponentProviderInterface, TurboStreamProviderInterface
{
    public function __construct(
        private EmergencyContactReadMapper $emergencyContactReadMapper,
    ) {
    }

    public function getView(object $data, ?HandlerContext $context = null): SheetView
    {
        return new SheetView(
            title: 'Modifier',
            description: 'Modifier le contact d\'urgence',
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

    public function getStreamView(object $data, ?FlashMessage $flashMessage = null, ?HandlerContext $context = null): EmergencyContactView
    {
        return $this->emergencyContactReadMapper->mapToView($data);
    }
}
