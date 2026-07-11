<?php

declare(strict_types=1);

namespace App\State\RegistrationStep\Provider;

use App\Dto\View\DialogModalView;
use App\Entity\RegistrationStep;
use App\Mapper\DestructiveModalMapper;
use App\State\Interface\FormComponentProviderInterface;

class RegistrationStepDeleteProvider implements FormComponentProviderInterface
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    ) {
    }
    public function mapToView(object $entity): DialogModalView
    {
        /** @var RegistrationStep $entity */
        return $this->destructiveModalMapper->mapToView(sprintf(
            'Etes vous certain de supprimer l\'étape <b>%s</b> ?',
            $entity->getTitle()
        ));
    }
}
