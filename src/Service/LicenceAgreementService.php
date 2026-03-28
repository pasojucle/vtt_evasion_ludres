<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\LicenceAgreement;

class LicenceAgreementService
{
    public function toHtml(LicenceAgreement $licenceAgreement): array
    {
        $agreement = $licenceAgreement->getAgreement();
        return ($licenceAgreement->isAgreed())
            ? [
                'color' => 'success',
                'icon' => $agreement->getAuthorizationIcon(),
                'message' => $agreement->getAuthorizationMessage(),
            ]
            : [
                'color' => 'danger',
                'icon' => $agreement->getRejectionIcon(),
                'message' => $agreement->getRejectionMessage(),
            ];
    }
}
