<?php

declare(strict_types=1);

namespace App\Mapper\Level;

use App\Dto\Enum\ColorVariant;
use App\Dto\View\BadgeView;
use App\Entity\Level;

class LevelBadgeMapper
{
    public function mapToView(?Level $level): BadgeView
    {
        if ($level) {
            return new BadgeView(
                value: $level->getTitle(),
                color: $level->getColor(),
            );
        }

        return new BadgeView(
            value: 'Aucun',
            variant: ColorVariant::WARNING,
        );
    }
}
