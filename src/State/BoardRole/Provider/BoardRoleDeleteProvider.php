<?php

declare(strict_types=1);

namespace App\State\BoardRole\Provider;

use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\State\ViewContext;
use App\Dto\View\DialogModalView;
use App\Entity\BoardRole;
use App\Mapper\DestructiveModalMapper;

/**
 * @implements FormComponentProviderInterface<BoardRole>
 */
class BoardRoleDeleteProvider implements FormComponentProviderInterface
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    ) {
    }

    public function getView(object $data, ?HandlerContext $context = null): DialogModalView
    {
        return $this->destructiveModalMapper->mapToView(sprintf('Etes vous certain de supprimer le role %s', $data->getName()));
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
