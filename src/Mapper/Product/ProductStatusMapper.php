<?php

declare(strict_types=1);

namespace App\Mapper\Product;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\PublishStatus;
use App\Dto\View\BadgeView;
use App\Entity\Product;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProductStatusMapper
{
    public function __construct(
        private TranslatorInterface $translator,
    ) {
    }

    public function mapToView(Product $entity, string $toggleStatusId): BadgeView
    {
        if ($entity->isDeleted()) {
            return new BadgeView(
                value :'Supprimée',
                variant: ColorVariant::DESTRUCTIVE,
                toggleStatusId: $toggleStatusId,
            );
        }

        $status = $entity->isDisabled() ? PublishStatus::DISABLED : PublishStatus::ENABLED;
        return new BadgeView(
            value: $status->trans($this->translator),
            variant: $status->variant(),
            toggleStatusId:$toggleStatusId,
        );
    }
}
