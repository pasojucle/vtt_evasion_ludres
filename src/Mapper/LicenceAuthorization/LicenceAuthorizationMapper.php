<?php

declare(strict_types=1);

namespace App\Mapper\LicenceAuthorization;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;
use App\Dto\View\BadgeView;
use App\Dto\View\LicenceAuthorization\LicenceAuthorizationView;
use App\Entity\LicenceAgreement;

class LicenceAuthorizationMapper
{
    public function mapToview(LicenceAgreement $licenceAgreement): LicenceAuthorizationView
    {
        $agreement = $licenceAgreement->getAgreement();
        if ($licenceAgreement->isAgreed()) {

            return new LicenceAuthorizationView( 
                $licenceAgreement->getId(),
                $agreement->getTitle(),
                $agreement->getAuthorizationMessage(),
                new BadgeView(
                    value: $agreement->getAuthorizationIcon(),
                    variant: ColorVariant::SUCCESS,
                    size: Size::ICON,
                )
            );
        }

        return new LicenceAuthorizationView( 
            $licenceAgreement->getId(),
            $agreement->getTitle(),
            $agreement->getRejectionMessage(),
            new BadgeView(
                value: $agreement->getRejectionIcon(),
                variant: ColorVariant::DESTRUCTIVE,
                size: Size::ICON,
            )
        );
    }
}
