<?php

declare(strict_types=1);

namespace App\State\Health\Provider;

use App\Dto\State\TurboStreamContext;
use App\Dto\View\Health\HealthView;
use App\Dto\View\SheetView;
use App\Entity\Health;
use App\Mapper\Health\HealthReadMapper;

use App\State\Interface\TurboStreamProviderInterface;

/**
 * @implements TurboStreamProviderInterface<Health>
 */
class HealthReadProvider implements TurboStreamProviderInterface
{
    public function __construct(
        private HealthReadMapper $identityReadMapper,
    ) {
    }

    public function getFormView(object $entity, ?string $fallback = null): SheetView
    {
        return new SheetView(
            title: 'Modifier',
            description: 'Modifier les informations sanitaires',
            action: 'Modifier'
        );
    }

    public function getFormOptions(object $entity): array
    {
        return [];
    }

    public function getStreamView(object $entity, ?TurboStreamContext $context = null): HealthView
    {
        return $this->identityReadMapper->mapToView($entity);
    }
}
