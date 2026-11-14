<?php

declare(strict_types=1);

namespace App\State\SecondHandCategory\Provider;

use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\View\DialogModalView;
use App\Entity\SecondHandCategory;
use App\Mapper\DestructiveModalMapper;

/**
 * @implements  FormComponentProviderInterface<SecondHandCategory>
 */
class SecondHandCategoryDeleteProvider implements FormComponentProviderInterface
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    ) {
    }
    public function getView(object $data, ?HandlerContext $context = null): DialogModalView
    {
        return $this->destructiveModalMapper->mapToView(
            sprintf(
                'Etes vous certain de supprimer la catégorie %s',
                $data->getName()
            )
        );
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
