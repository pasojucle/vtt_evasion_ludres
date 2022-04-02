<?php

declare(strict_types=1);

namespace App\ViewModel;

class AbstractPresenter
{
    public function __construct(
        public ServicesPresenter $services
    ) {
    }
}
