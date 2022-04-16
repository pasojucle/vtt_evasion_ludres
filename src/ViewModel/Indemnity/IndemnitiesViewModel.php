<?php

declare(strict_types=1);

namespace App\ViewModel\Indemnity;

class IndemnitiesViewModel
{
    public ?array $indemnities = [];

    public static function fromIndemnities(array $indemnities): IndemnitiesViewModel
    {
        $indemnitiesViewModel = [];
        if (!empty($indemnities)) {
            foreach ($indemnities as $indemnity) {
                $indemnityView = IndemnityViewModel::fromIndemnity($indemnity);
                $indemnitiesViewModel[$indemnityView->level->getId()][$indemnity->getBikeRideType()] = $indemnityView;
            }
        }
        dump($indemnitiesViewModel);
        $indemnitiesView = new self();
        $indemnitiesView->indemnities = $indemnitiesViewModel;

        return $indemnitiesView;
    }
}
