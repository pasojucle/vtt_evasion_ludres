<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\LicenceAgreementDto;
use App\Entity\LicenceAgreement;
use Doctrine\Common\Collections\Collection;

class LicenceAgreementDtoTransformer
{
    public function fromEntity(?LicenceAgreement $licenceAgreement): LicenceAgreementDto
    {
        $licenceAgreementDto = new LicenceAgreementDto();
        if ($licenceAgreement) {
            $licenceAgreementDto->id = $licenceAgreement->getId();
            $licenceAgreementDto->title = $licenceAgreement->getAgreement()->getTitle();
            $licenceAgreementDto->agreed = $licenceAgreement->isAgreed();
            $licenceAgreementDto->toString = ($licenceAgreement->isAgreed()) ? 'autorise' : 'n\'autorise pas';
            $licenceAgreementDto->toHtml = $this->toHtml($licenceAgreement);
            $licenceAgreementDto->content = $licenceAgreement->getAgreement()->getContent();
            $licenceAgreementDto->kind = $licenceAgreement->getAgreement()->getKind();
        }

        return $licenceAgreementDto;
    }

    public function fromEntities(Collection|array $licenceAgreementEntities): array
    {
        $licencesAgreements = [];
        foreach ($licenceAgreementEntities as $licenceAgreementEntity) {
            $licenceAgreement = $this->fromEntity($licenceAgreementEntity);
            $agreement = $licenceAgreementEntity->getAgreement();
            $licencesAgreements[$agreement->getId()] = $licenceAgreement;
        }

        return $licencesAgreements;
    }

    private function toHtml(LicenceAgreement $licenceAgreement): array
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
