<?php

declare(strict_types=1);

namespace App\State\SlideshowDirectory\Provider;

use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\View\DialogModalView;
use App\Entity\SlideshowDirectory;
use App\Mapper\DestructiveModalMapper;

/**
 * @implements FormComponentProviderInterface<SlideshowDirectory>
 */
class SlideshowDirectoryDeleteProvider implements FormComponentProviderInterface
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    ) {
    }

    public function getView(object $data, ?HandlerContext $context = null): DialogModalView
    {
        return $this->destructiveModalMapper->mapToView(sprintf(
            $data->getSlideshowImages()->isEmpty()
            ? 'Etes vous certain de supprimer le répetroire %s ?'
            : 'Etes vous certain de supprimer le répetroire %s et tous les fichiers qu\'il contient ?',
            $data->getName()
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
