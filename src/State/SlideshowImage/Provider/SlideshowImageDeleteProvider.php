<?php

declare(strict_types=1);

namespace App\State\SlideshowImage\Provider;

use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\View\DialogModalView;
use App\Entity\SlideshowImage;
use App\Mapper\DestructiveModalMapper;

/**
 * @implements FormComponentProviderInterface<SlideshowImage>
 */
class SlideshowImageDeleteProvider implements FormComponentProviderInterface
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    ) {
    }
    
    public function getView(object $entity, ?HandlerContext $context = null): DialogModalView
    {
        return $this->destructiveModalMapper->mapToView(sprintf(
            'Etes vous certain de supprimer l\'image <b>%s</b> ?',
            $entity->getFilename()
        ));
    }

    public function getFormOptions(object $data, ?HandlerContext $context = null): array
    {
        return [
            'attr' => [
                'data-action' => 'turbo:submit-end->modal#handleFormSubmit',
            ]
        ];
    }
}
