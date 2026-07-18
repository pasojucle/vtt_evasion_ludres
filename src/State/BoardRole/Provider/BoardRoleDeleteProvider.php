<?php

declare(strict_types=1);

namespace App\State\BoardRole\Provider;

use App\Dto\View\DialogModalView;
use App\Entity\BoardRole;
use App\Mapper\DestructiveModalMapper;
use App\State\Interface\FormComponentProviderInterface;

/**
 * @implements FormComponentProviderInterface<BoardRole>
 */
class BoardRoleDeleteProvider implements FormComponentProviderInterface
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    ) {
    }

    public function mapToView(object $entity, ?string $fallback = null): DialogModalView
    {
        return $this->destructiveModalMapper->mapToView(sprintf('Etes vous certain de supprimer le role %s', $entity->getName()));
    }
}
