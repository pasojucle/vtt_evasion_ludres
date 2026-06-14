<?php

declare(strict_types=1);

namespace App\State\SlideshowDirectory\Provider;

use App\Dto\DialogModalView;
use App\Entity\SlideshowDirectory;
use App\Mapper\DestructiveModalMapper;

class SlideshowDirectoryDeleteProvider
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    ) {
    }
    public function mapToView(SlideshowDirectory  $entity): DialogModalView
    {
        return $this->destructiveModalMapper->mapToView(sprintf(
            $$entity->getSlideshowImages()->isEmpty()
            ? 'Etes vous certain de supprimer le répetroire %s ?'
            : 'Etes vous certain de supprimer le répetroire %s et tous les fichiers qu\'il contient ?',
            $entity->getName()
        ));
    }
}
