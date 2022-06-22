<?php

declare(strict_types=1);

namespace App\ViewModel\Background;

use App\Entity\Background;
use App\ViewModel\AbstractViewModel;
use App\ViewModel\ServicesPresenter;


class BackgroundViewModel extends AbstractViewModel
{
    public ?Background $entity;

    public ?string $filename;

    public ?string $path;

    private ServicesPresenter $services;


    public static function fromBackground(Background $background, ServicesPresenter $services)
    {
        $backgroundView = new self();
        $backgroundView->entity = $background;
        $backgroundView->services = $services;
        $backgroundView->filename = $background->getFilename();
        $backgroundView->path = $backgroundView->getPath();

        return $backgroundView;
    }

    

    private function getPath(): ?string
    {
        return ($this->entity->getFileName()) ? $this->services->uploadsDirectory.$this->entity->getFileName() : null;

    }
}
