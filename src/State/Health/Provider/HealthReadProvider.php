<?php

declare(strict_types=1);

namespace App\State\Health\Provider;

use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Contract\Provider\TurboStreamProviderInterface;
use App\Core\Dto\FlashMessage;
use App\Core\Dto\HandlerContext;
use App\Dto\View\Health\HealthView;
use App\Dto\View\SheetView;
use App\Entity\Health;
use App\Mapper\Health\HealthReadMapper;

/**
 * @implements TurboStreamProviderInterface<Health>
 */
class HealthReadProvider implements FormComponentProviderInterface, TurboStreamProviderInterface
{
    public function __construct(
        private HealthReadMapper $identityReadMapper,
    ) {
    }

    public function getView(object $data, ?HandlerContext $context = null): SheetView
    {
        return new SheetView(
            title: 'Modifier',
            description: 'Modifier les informations sanitaires',
            action: 'Modifier'
        );
    }

    public function getFormOptions(object $data): array
    {
        return [
            'attr' => [
                'data-action' => 'turbo:submit-end->sheet#handleFormSubmit',
            ],
        ];
    }

    public function getStreamView(object $entity, ?FlashMessage $flashMessage = null, ?HandlerContext $context = null): HealthView
    {
        return $this->identityReadMapper->mapToView($entity);
    }
}
