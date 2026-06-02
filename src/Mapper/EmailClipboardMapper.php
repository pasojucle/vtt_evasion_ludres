<?php

declare(strict_types=1);

namespace App\Mapper;


class EmailClipboardMapper
{
    public function mapToEmailCsvString(array $entities): string
    {
        $emails = [];
        foreach ($entities as $entity) {
            $emails[] = $entity->getIdentity()->getEmail();
        }

        return implode(',', $emails);
    }
}
