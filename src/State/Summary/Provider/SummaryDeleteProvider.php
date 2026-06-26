<?php

declare(strict_types=1);

namespace App\State\Summary\Provider;

use App\Dto\View\DialogModalView;
use App\Entity\Summary;
use App\Mapper\DestructiveModalMapper;
use App\State\FormComponentProviderInterface;

class SummaryDeleteProvider implements FormComponentProviderInterface
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    ) {
    }
    
    /**
     * @implements FormComponentProviderInterface<Summary>
     */
    public function mapToView(object $entity): DialogModalView
    {
        return $this->destructiveModalMapper->mapToView(sprintf(
            'Etes vous certain de supprimer l\'actualité <b>%s</b> ?',
            $entity->getTitle()
        ));
    }
}
