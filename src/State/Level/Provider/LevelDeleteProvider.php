<?php

declare(strict_types=1);

namespace App\State\Level\Provider;

use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\View\DialogModalView;
use App\Mapper\DestructiveModalMapper;

class LevelDeleteProvider implements FormComponentProviderInterface
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    ) {
    }
    public function getView(object $data, ?HandlerContext $context = null): DialogModalView
    {
        return $this->destructiveModalMapper->mapToView(sprintf('Etes vous certain de supprimer le niveau %s', $data->getTitle()));
    }

    public function getFormOptions(object $data, ?HandlerContext $context = null): array
    {
        return [
            'attr' => [
                'data-action' => 'turbo:submit-end->modal#handleFormSubmit',
            ],
        ];
    }
}
