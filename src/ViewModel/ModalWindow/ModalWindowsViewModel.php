<?php

declare(strict_types=1);

namespace App\ViewModel\ModalWindow;

use App\Entity\ModalWindow;
use App\Entity\OrderHeader;
use App\Entity\Survey;
use App\ViewModel\ServicesPresenter;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Tools\Pagination\Paginator;

class ModalWindowsViewModel
{
    public ?array $modalWindows = [];

    public static function fromModalWindows(array|Paginator|Collection $modalWindows, ServicesPresenter $services): ModalWindowsViewModel
    {
        $modalWindowsViewModel = [];
        if (!empty($modalWindows)) {
            foreach ($modalWindows as $modalWindow) {
                if ($modalWindow instanceof ModalWindow) {
                    $modalWindowsViewModel[] = ModalWindowViewModel::fromModalWindow($modalWindow, $services);
                }
                if ($modalWindow instanceof Survey) {
                    $modalWindowsViewModel[] = ModalWindowViewModel::fromSuvey($modalWindow, $services);
                }
                if ($modalWindow instanceof OrderHeader) {
                    $modalWindowsViewModel[] = ModalWindowViewModel::fromOrderHeader($modalWindow, $services);
                }
            }
        }

        $modalWindowsView = new self();
        $modalWindowsView->modalWindows = $modalWindowsViewModel;

        return $modalWindowsView;
    }
}
