<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\SurveyResponseDto;

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
            $member = $user->getIdentity();
            $fullName = sprintf('%s %s', mb_strtoupper($member->getName()), mb_ucfirst($member->getFirstName()));
            $mainEmail = $user->getMainIdentity()?->getEmail();
        }

        return [
            'fullName' => $fullName,
            'mainEmail' => $mainEmail,
        ];
    }
}
