<?php

declare(strict_types=1);

namespace App\State\SlideshowImage\Provider;

use App\Dto\View\DialogModalView;
use App\Entity\SlideshowImage;
use App\Mapper\DestructiveModalMapper;
use App\State\FormComponentProviderInterface;

class SlideshowImageDeleteProvider implements FormComponentProviderInterface
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    ) {
    }
    
    /**
     * @implements FormComponentProviderInterface<SlideshowImage>
     */
    public function mapToView(object $entity): DialogModalView
    {
        return $this->destructiveModalMapper->mapToView(sprintf(
            'Etes vous certain de supprimer l\'image <b>%s</b> ?',
            $entity->getFilename()
        ));
    }
}
