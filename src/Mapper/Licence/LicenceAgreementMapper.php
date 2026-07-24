<?php

declare(strict_types=1);

namespace App\Mapper\Licence;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;
use App\Dto\View\BadgeView;
use App\Entity\LicenceAgreement;

class LicenceAgreementMapper
{
    public function mapToview(LicenceAgreement $licenceAgreement): BadgeView
    {
        $agreement = $licenceAgreement->getAgreement();
        if ($licenceAgreement->isAgreed()) {
            return new BadgeView(
                value: $agreement->getAuthorizationIcon(),
                variant: ColorVariant::SUCCESS,
                size: Size::ICON,
            );
        }

        return new BadgeView(
            value: $agreement->getRejectionIcon(),
            variant: ColorVariant::DESTRUCTIVE,
            size: Size::ICON,
        );

    }
}
