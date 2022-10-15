<?php

declare(strict_types=1);

namespace App\ViewModel\ModalWindow;

use ReflectionClass;
use App\Entity\Survey;
use App\Entity\ModalWindow;
use App\Entity\OrderHeader;
use App\ViewModel\AbstractViewModel;
use App\ViewModel\ServicesPresenter;


class ModalWindowViewModel extends AbstractViewModel
{
    public ?ModalWindow $entity;

    public ?string $index;

    public ?string $title;

    public ?string $content;

    public ?string $url;

    public ?string $labelButton;

    private ServicesPresenter $services;

    public static function fromModalWindow(ModalWindow $modalWindow, ServicesPresenter $services)
    {
        $modalWindowView = new self();
        $modalWindowView->services = $services;
        $modalWindowView->index = $modalWindowView->getIndex($modalWindow);
        $modalWindowView->title = $modalWindow->getTitle();
        $modalWindowView->content = $modalWindow->getContent();

        return $modalWindowView;
    }

    public static function fromSuvey(Survey $survey, ServicesPresenter $services)
    {
        $modalWindowView = new self();
        $modalWindowView->services = $services;
        $modalWindowView->index = $modalWindowView->getIndex($survey);
        $modalWindowView->title = $survey->getTitle();
        $modalWindowView->content = $survey->getContent();
        $modalWindowView->url = $services->router->generate('survey', ['survey' => $survey->getId()]);
        $modalWindowView->labelButton = 'Participer';

        return $modalWindowView;
    }

    public static function fromOrderHeader(OrderHeader $orderHeader, ServicesPresenter $services)
    {
        $modalWindowView = new self();
        $modalWindowView->services = $services;
        $modalWindowView->index = $modalWindowView->getIndex($orderHeader);
        $modalWindowView->title = 'Commande en cours';
        $modalWindowView->content = $services->modalWindowOrderInProgress;
        $modalWindowView->url = $services->router->generate('order_edit');
        $modalWindowView->labelButton = 'Valider ma commande';

        return $modalWindowView;
    }

    private function getIndex(Survey|OrderHeader|ModalWindow $entity)
    {
        return $this->services->security->getUser()->getLicenceNumber() . '-' . (new ReflectionClass($entity))->getShortName() . '-' . $entity->getId();
    }
}