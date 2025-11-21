<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\SurveyResponseDto;

use App\Entity\Enum\IdentityKindEnum;
use App\Entity\Enum\LicenceCategoryEnum;
use App\Entity\Identity;
use App\Entity\Licence;
use App\Entity\SurveyIssue;
use App\Entity\SurveyResponse;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use Symfony\Contracts\Translation\TranslatorInterface;

class SurveyResponseDtoTransformer
{
    public function __construct(
        private TranslatorInterface $translator
    ) {
    }


    public function fromEntity(SurveyResponse $surveyResponse): SurveyResponseDto
    {
        $surveyResponseDto = new SurveyResponseDto();
        $surveyResponseDto->issue = $surveyResponse->getSurveyIssue()->getContent();
        $surveyResponseDto->user = $this->getUser($surveyResponse->getUser());
        $surveyResponseDto->value = (null !== $surveyResponse->getValue() && SurveyIssue::RESPONSE_TYPE_STRING !== $surveyResponse->getSurveyIssue()->getResponseType())
            ? $this->translator->trans(SurveyResponse::VALUES[$surveyResponse->getValue()])
            : $surveyResponse->getValue();
        $surveyResponseDto->uuid = $surveyResponse->getUuid();

        return $surveyResponseDto;
    }

    public function fromEntities(Collection|array $surveyResponseEntities): array
    {
        $surveyResponses = [];
        foreach ($surveyResponseEntities as $surveyResponseEntity) {
            $surveyResponses[] = $this->fromEntity($surveyResponseEntity);
        }

        return $surveyResponses;
    }

    public function getUser(?User $user): array
    {
        $fullName = '';
        $mainEmail = '';
        if ($user instanceof User) {
            $identities = $this->getIdentities($user);
            $licence = $user->getLastLicence();
            $member = $identities[IdentityKindEnum::MEMBER->name];
            $fullName = sprintf('%s %s', mb_strtoupper($member->getName()), mb_ucfirst($member->getFirstName()));
            $mainEmail = $this->getMainEmail($identities, $licence->getCategory());
        }

        return [
            'fullName' => $fullName,
            'mainEmail' => $mainEmail,
        ];
    }

    public function getIdentities(User $user): array
    {
        $identities = [];
        /** @var Identity $identity */
        foreach ($user->getIdentities() as $identity) {
            $identities[$identity->getKind()->name] = $identity;
        }


        return $identities;
    }

    private function getMainEmail(array $identitiesByType, LicenceCategoryEnum $category): ?string
    {
        if (!empty($identitiesByType)) {
            $identity = (LicenceCategoryEnum::SCHOOL === $category && array_key_exists(IdentityKindEnum::KINSHIP->name, $identitiesByType))
                ? $identitiesByType[IdentityKindEnum::KINSHIP->name]
                : $identitiesByType[IdentityKindEnum::MEMBER->name];
            return $identity->getEmail();
        }

        return '';
    }
}
