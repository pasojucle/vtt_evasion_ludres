<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\LicenceAgreementDto;
use App\Entity\LicenceAgreement;
use App\Service\LicenceAgreementService;
use Doctrine\Common\Collections\Collection;

class LicenceAgreementDtoTransformer
{
    public function __construct(
        private LicenceAgreementService $licenceAgreementService
    ) {
    }

    public function fromEntity(?LicenceAgreement $licenceAgreement): LicenceAgreementDto
    {
        $licenceAgreementDto = new LicenceAgreementDto();
        if ($licenceAgreement) {
            $licenceAgreementDto->id = $licenceAgreement->getId();
            $licenceAgreementDto->title = $licenceAgreement->getAgreement()->getTitle();
            $licenceAgreementDto->agreed = $licenceAgreement->isAgreed();
            $licenceAgreementDto->toString = ($licenceAgreement->isAgreed()) ? 'autorise' : 'n\'autorise pas';
            $licenceAgreementDto->toHtml = $this->licenceAgreementService->toHtml($licenceAgreement);
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
}
