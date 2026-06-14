<?php

declare(strict_types=1);

namespace App\Mapper;

use App\Entity\Member;


class EmailClipboardMapper
{
    public function mapToEmailCsvString(array $entities): string
    {
        $emails = [];
        /** @var Member $entity */
        foreach ($entities as $entity) {
            $emails[] = $entity->getIdentity()->getEmail();
        }

        return implode(',', $emails);
    }
}
