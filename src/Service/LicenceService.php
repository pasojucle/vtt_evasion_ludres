<?php

declare(strict_types=1);

namespace App\Service;

use DateTime;
use App\Entity\User;
use App\Entity\Licence;

class LicenceService
{
    public function getCategory(User $user): int
    {
        $today = new DateTime();
        $age = $today->diff($user->getIdentities()->first()->getBirthDate());

        return (18 > (int) $age->format('%y')) ? Licence::CATEGORY_MINOR : Licence::CATEGORY_ADULT;
    }
}
