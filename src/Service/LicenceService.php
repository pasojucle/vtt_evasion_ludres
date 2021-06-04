<?php

namespace App\Service;

use DateTime;

class LicenceService
{
    public function getCurrentSeason():int
    {
        $today = new DateTime();
        return (8 < (int) $today->format('m')) ? (int) $today->format('Y') + 1 :  (int) $today->format('Y');
    }
}