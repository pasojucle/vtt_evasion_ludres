<?php

declare(strict_types=1);

namespace App\Mapper\User;

use App\Entity\Member;

class UserAutocompleteMapper
{
    /**
     * @return list<array{value: int|string, text: string}>
     */
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

        return $results;
    }
}
