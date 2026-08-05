<?php

declare(strict_types=1);

namespace App\Mapper\User;

use App\Entity\Member;

class UserAutocompleteMapper
{
    public function mapToChoices(array $users): array
    {
        $results = [];
        /** @var Member $user */
        foreach ($users as $user) {
            $results[] = [
                'value' => $user->getId(),
                'text' => $user->getIdentity()->getFullName(),
            ];
        }
        dump($results);
        return $results;
    }
}
