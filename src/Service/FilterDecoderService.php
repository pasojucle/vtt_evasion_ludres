<?php

declare(strict_types=1);

namespace App\Service;

class FilterDecoderService
{
    /**
     */
    public function decode(?string $encodedFilter): array
    {
        if (!$encodedFilter) {
            return [];
        }

        $decoded = base64_decode($encodedFilter, true);
        if (!$decoded) {
            return [];
        }

        parse_str($decoded, $params);

        return $params;
    }
}