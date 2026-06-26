<?php

declare(strict_types=1);

namespace App\State\Licence\Provider;

use App\Dto\View\DialogModalView;
use App\Entity\Licence;
use App\Mapper\DestructiveModalMapper;
use App\State\FormComponentProviderInterface;

class LicenceDeleteProvider implements FormComponentProviderInterface
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    ) {
    }
    public function mapToView(object $entity): DialogModalView
    {
        /** @var Licence $entity */
        return $this->destructiveModalMapper->mapToView(
            sprintf(
                'Etes vous certain de supprimer l\'inscription de <b>%s</b> ?',
                $entity->getMember()->getIdentity()->getFullName()
            )
        );
    }
}
