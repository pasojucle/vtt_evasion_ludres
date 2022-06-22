<?php

declare(strict_types=1);

namespace App\ViewModel\Background;

use App\ViewModel\ServicesPresenter;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Tools\Pagination\Paginator;

class BackgroundsViewModel
{
    public ?array $backgrounds = [];

    public static function fromBackgrounds(array|Paginator|Collection $backgrounds, ServicesPresenter $services): BackgroundsViewModel
    {
        $backgroundsViewModel = [];
        if (!empty($backgrounds)) {
            foreach ($backgrounds as $background) {
                $backgroundsViewModel[] = BackgroundViewModel::fromBackground($background, $services);
            }
        }

        $backgroundsView = new self();
        $backgroundsView->backgrounds = $backgroundsViewModel;

        return $backgroundsView;
    }
}
