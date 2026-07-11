<?php

declare(strict_types=1);

namespace App\Mapper\Survey;

use App\Dto\Enum\PublishStatus;
use App\Dto\View\BadgeView;
use App\Entity\Survey;
use Symfony\Contracts\Translation\TranslatorInterface;

class SurveyStatusMapper {

    public function __construct(
        private TranslatorInterface $translator,
    ){}

    public function mapToView(Survey $entity, string $toggleStatusId): BadgeView
    {
        $status = $entity->isDisabled() ? PublishStatus::DISABLED : PublishStatus::ENABLED;
        
        return new BadgeView(
            value: $status->trans($this->translator), 
            variant: $status->variant(),
            toggleStatusId:$toggleStatusId,
        );
    }
}