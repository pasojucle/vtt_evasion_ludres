<?php

declare(strict_types=1);

namespace App\State\EmergencyContact\Provider;

use App\Core\Contract\Provider\ComponentProviderInterface;
use App\Core\Dto\FlashMessage;
use App\Core\Dto\HandlerContext;
use App\Dto\View\EmergencyContact\EmergencyContactView;
use App\Entity\EmergencyContact;
use App\Mapper\EmergencyContact\EmergencyContactReadMapper;

/**
 * @implements ComponentProviderInterface<EmergencyContact>
 */
class EmergencyContactReadProvider implements ComponentProviderInterface
{
    public function __construct(
        private EmergencyContactReadMapper $emergencyContactReadMapper,
    ) {
    }

    public function getView(object $data, ?HandlerContext $context = null): EmergencyContactView
    {
        return $this->emergencyContactReadMapper->mapToView($data);
    }
}
