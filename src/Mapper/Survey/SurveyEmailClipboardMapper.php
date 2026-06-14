<?php

declare(strict_types=1);

namespace App\Mapper\Survey;

use App\Entity\Survey;


class SurveyEmailClipboardMapper
{
    public function mapToEmailCsvString(Survey $entity): string
    {
        $emails = [];
        /** @var Survey $entity */
        foreach ($entities as $entity) {
            $emails[] = $entity->getIdentity()->getEmail();
        }

        return implode(',', $emails);
    }
}
