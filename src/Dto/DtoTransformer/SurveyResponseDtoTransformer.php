<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\DtoTransformer\UserDtoTransformer;

use App\Dto\SurveyResponseDto;
use App\Entity\SurveyIssue;
use App\Entity\SurveyResponse;
use Doctrine\Common\Collections\Collection;
use Symfony\Contracts\Translation\TranslatorInterface;

class SurveyResponseDtoTransformer
{
    public function __construct(
        private UserDtoTransformer $userDtoTransformer,
        private TranslatorInterface $translator
    ) {
    }


    public function fromEntity(SurveyResponse $surveyResponse): SurveyResponseDto
    {
        $surveyResponseDto = new SurveyResponseDto();
        $surveyResponseDto->issue = $surveyResponse->getSurveyIssue()->getContent();
        $surveyResponseDto->user = (null !== $surveyResponse->getUser())
            ? $this->userDtoTransformer->fromEntity($surveyResponse->getUser())
            : null;
        $surveyResponseDto->value = (SurveyIssue::RESPONSE_TYPE_STRING !== $surveyResponse->getSurveyIssue()->getResponseType())
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
}
