<?php

declare(strict_types=1);

namespace App\State\Licence\Provider;

use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\View\DialogModalView;
use App\Entity\Licence;
use App\Mapper\DestructiveModalMapper;

/**
 * @implements FormComponentProviderInterface<Licence>
 */
class LicenceDeleteProvider implements FormComponentProviderInterface
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    ) {
    }

    public function getView(object $data, ?HandlerContext $context = null): DialogModalView
    {
        return $this->destructiveModalMapper->mapToView(
            sprintf(
                'Etes vous certain de supprimer l\'inscription de <b>%s</b> ?',
                $data->getMember()->getIdentity()->getFullName()
            )
        );
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
