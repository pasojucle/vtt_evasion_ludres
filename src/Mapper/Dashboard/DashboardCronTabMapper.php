<?php

declare(strict_types=1);

namespace App\Mapper\Dashboard;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;
use App\Dto\View\BadgeView;
use App\Dto\View\Dashboard\DashboardBikeRideView;
use App\Dto\View\Dashboard\DashboardClusterView;
use App\Dto\View\Dashboard\DashboardItemView;
use App\Entity\BikeRide;
use App\Entity\Enum\LevelType;
use App\Entity\Enum\RegistrationEnum;
use DateInterval;
use DateTime;

class DashboardCronTabMapper
{
    public function mapToView(int $executeAt): DashboardItemView
    {
        $onError = $executeAt < (new DateTime())->setTime(12, 0, 0)->sub(new DateInterval('P1D'))->getTimestamp();
        return new DashboardItemView(
            badge: $onError
                ? new BadgeView(
                    value: 'lucide:circle-alert',
                    variant: ColorVariant::DESTRUCTIVE,
                    size: Size::ICON
                )
                : new BadgeView(
                    value: 'lucide:circle-check',
                    variant: ColorVariant::SUCCESS,
                    size: Size::ICON
                ),
            label: date("d/m/Y H:i:s.", $executeAt)
        );
    }
}
