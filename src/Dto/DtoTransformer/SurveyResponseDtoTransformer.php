<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\SurveyResponseDto;

use App\Entity\Member;
use App\Entity\SurveyIssue;
use App\Entity\SurveyResponse;
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
        $surveyResponseDto->user = $this->getUser($surveyResponse->getMember());
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

    public function getUser(?Member $member): array
    {
        $fullName = '';
        $mainEmail = '';
        if ($member instanceof Member) {
            $identity = $member->getIdentity();
            $fullName = sprintf('%s %s', mb_strtoupper($identity->getName()), mb_ucfirst($identity->getFirstName()));
            $mainEmail = $member->getContactEmail();
        }

        return [
            'fullName' => $fullName,
            'mainEmail' => $mainEmail,
        ];
    }
}
