<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;


use App\Dto\SurveyDto;
use App\Entity\Survey;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Tools\Pagination\Paginator;

class SurveyDtoTransformer
{
    public function fromEntity(Survey $survey): SurveyDto
    {
        $surveyDto = new surveyDto;
        $surveyDto->id = $survey->getId();
        $surveyDto->entity = $survey;
        $surveyDto->title = $survey->getTitle();
        $surveyDto->content = $survey->getContent();
   

        return $surveyDto;
    }

    public function fromEntities(Paginator|Collection|array $surveyEntities): array
    {
        $surveys = [];
        foreach($surveyEntities as $surveyEntity) {
            $surveys[] = $this->fromEntity($surveyEntity);
        }

        return $surveys;
    }
}