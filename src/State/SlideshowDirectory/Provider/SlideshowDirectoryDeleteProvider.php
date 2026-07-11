<?php

declare(strict_types=1);

namespace App\State\SlideshowDirectory\Provider;

use App\Dto\View\DialogModalView;
use App\Entity\SlideshowDirectory;
use App\Mapper\DestructiveModalMapper;
use App\State\Interface\FormComponentProviderInterface;

class SlideshowDirectoryDeleteProvider implements FormComponentProviderInterface
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    ) {
    }

    /**
     * @implements FormComponentProviderInterface<SlideshowDirectory>
     */
    public function mapToView(object $entity): DialogModalView
    {
        return $this->destructiveModalMapper->mapToView(sprintf(
            $$entity->getSlideshowImages()->isEmpty()
            ? 'Etes vous certain de supprimer le répetroire %s ?'
            : 'Etes vous certain de supprimer le répetroire %s et tous les fichiers qu\'il contient ?',
            $entity->getName()
        ));
    }
}
