<?php

declare(strict_types=1);

namespace App\ViewModel\ModalWindow;

use App\Entity\Licence;
use App\Entity\ModalWindow;
use App\Entity\OrderHeader;
use App\Entity\Survey;
use App\Entity\User;
use App\ViewModel\AbstractViewModel;
use App\ViewModel\LicenceViewModel;
use App\ViewModel\ServicesPresenter;
use ReflectionClass;

class ModalWindowViewModel extends AbstractViewModel
{
    public ?ModalWindow $entity;

    public ?string $index;

    public ?string $title;

    public ?string $content;

    public ?string $url;

    public ?string $labelButton;

    public static function fromModalWindow(ModalWindow $modalWindow, ServicesPresenter $services)
    {
        $modalWindowView = new self();
        $modalWindowView->index = $services->modalWindowService->getIndex($modalWindow);
        $modalWindowView->title = $modalWindow->getTitle();
        $modalWindowView->content = $modalWindow->getContent();

        return $modalWindowView;
    }

    public static function fromSuvey(Survey $survey, ServicesPresenter $services)
    {
        $modalWindowView = new self();
        $modalWindowView->index = $services->modalWindowService->getIndex($survey);
        $modalWindowView->title = $survey->getTitle();
        $modalWindowView->content = $survey->getContent();
        $modalWindowView->url = $services->router->generate('survey', ['survey' => $survey->getId()]);
        $modalWindowView->labelButton = 'Participer';

        return $modalWindowView;
    }

    public static function fromOrderHeader(OrderHeader $orderHeader, ServicesPresenter $services)
    {
        $modalWindowView = new self();
        $modalWindowView->index = $services->modalWindowService->getIndex($orderHeader);
        $modalWindowView->title = 'Commande en cours';
        $modalWindowView->content = $services->modalWindowOrderInProgress;
        $modalWindowView->url = $services->router->generate('order_edit');
        $modalWindowView->labelButton = 'Valider ma commande';

        return $modalWindowView;
    }

    public static function fromLicence(LicenceViewModel $licence, ServicesPresenter $services)
    {
        $modalWindowView = new self();
        $modalWindowView->index = $services->modalWindowService->getIndex($licence->entity);
        $modalWindowView->title = 'Dossier d\'inscription en cours';
        $modalWindowView->content = $services->modalWindowRegistrationInProgress;
        $modalWindowView->url = $services->router->generate('user_registration_form', ['step' => 1]);
        $modalWindowView->labelButton = 'Finaliser mon inscription';

        return $modalWindowView;
    }
}
