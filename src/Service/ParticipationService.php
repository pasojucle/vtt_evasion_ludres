<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

class ParticipationService
{
    public function paginateFromArray(array $users, Request $request, int $limit): array
    {
        $currentPage = $request->query->getInt('p') ?: 1;

        $offset = $limit * ($currentPage - 1);

        return array_slice($users, $offset, $limit);
    }
}
