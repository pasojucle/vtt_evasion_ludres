<?php

declare(strict_types=1);

namespace App\State\SurveyResponse\Provider;


use App\Dto\View\SurveyResponse\SurveyResponseListView;
use App\Entity\Survey;
use App\Mapper\SurveyResponse\SurveyResponseMapper;

class SurveyResponseAdminProvider
{    
    public function __construct(
        private SurveyResponseMapper $mapper,
    ) {
    }


    public function getCollection(Survey $entity): SurveyResponseListView
    {
        return $this->mapper->mapToView(
            $entity,
        );
    }
}
