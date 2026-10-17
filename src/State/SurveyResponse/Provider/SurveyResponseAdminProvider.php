<?php

declare(strict_types=1);

namespace App\State\SurveyResponse\Provider;

use App\Core\Dto\HandlerContext;
use App\Dto\View\SurveyResponse\SurveyResponseListView;
use App\Entity\Survey;
use App\Mapper\SurveyResponse\SurveyResponseListMapper;

class SurveyResponseAdminProvider
{
    public function __construct(
        private SurveyResponseListMapper $mapper,
    ) {
    }


    public function getCollection(Survey $entity, ?HandlerContext $context = null): SurveyResponseListView
    {
        return $this->mapper->mapToView(
            $entity,
            $context->encodedFallback,
        );
    }
}
