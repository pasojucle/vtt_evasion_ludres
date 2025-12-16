<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\AgreementDto;
use App\Entity\Agreement;
use App\Entity\Enum\LicenceCategoryEnum;
use App\Entity\Enum\LicenceMembershipEnum;
use Symfony\Contracts\Translation\TranslatorInterface;

class AgreementDtoTransformer
{
    public function __construct(
        private TranslatorInterface $translator
    ) {
    }

    public function fromEntity(Agreement $agreement): AgreementDto
    {
        $agreementDto = new AgreementDto();
        $agreementDto->id = $agreement->getId();
        $agreementDto->title = $agreement->getTitle();
        $agreementDto->category = $this->categoryToString($agreement->getCategory());
        $agreementDto->membership = $this->membershipToString($agreement->getMembership());
        $agreementDto->enabled = $agreement->isEnabled();

        return $agreementDto;
    }

    public function fromEntities(array $agreementEntities): array
    {
        $agreements = [];
        foreach ($agreementEntities as $agreementEntity) {
            $agreements[] = $this->fromEntity($agreementEntity);
        }

        return $agreements;
    }

    private function categoryToString(LicenceCategoryEnum $category): array
    {
        return [
            'icon' => match ($category) {
                LicenceCategoryEnum::SCHOOL => '<i class="fa-solid fa-school"></i>',
                LicenceCategoryEnum::ADULT => '<i class="fa-solid fa-person-biking"></i>',
                default => '<i class="fa-solid fa-school"></i> <i class="fa-solid fa-person-biking"></i>'
            },
            'title' => $category->trans($this->translator)
        ];
    }

    private function membershipToString(LicenceMembershipEnum $membership): array
    {
        return [
            'icon' => match ($membership) {
                LicenceMembershipEnum::TRIAL => '<i class="fa-solid fa-clipboard-question"></i>',
            LicenceMembershipEnum::YEARLY => '<i class="fa-solid fa-clipboard-check"></i>',
            default => '<i class="fa-solid fa-clipboard-question"></i> <i class="fa-solid fa-clipboard-check"></i>'
            },
            'title' => $membership->trans($this->translator)
        ];
    }
}
