<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\LicenceDto;
use App\Dto\ModalWindowDto;
use App\Entity\Licence;
use App\Entity\ModalWindow;
use App\Entity\OrderHeader;
use App\Entity\Survey;
use App\Service\ModalWindowService;
use App\Service\ParameterService;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ModalWindowDtoTransformer
{
    private ?string $modalWindowOrderInProgress;
    public ?string $modalWindowRegistrationInProgress;

    public function __construct(
        private ModalWindowService $modalWindowService,
        private UrlGeneratorInterface $router,
        private ParameterService $parameterService
    ) {
        $this->modalWindowOrderInProgress = $this->parameterService->getParameterByName('MODAL_WINDOW_ORDER_IN_PROGRESS');
        $this->modalWindowRegistrationInProgress = $this->parameterService->getParameterByName('MODAL_WINDOW_REGISTRATION_IN_PROGRESS');
    }

    public function fromModalWindow(ModalWindow $modalWindow): ModalWindowDto
    {
        $modalWindowDto = new ModalWindowDto();
        $modalWindowDto->index = $this->modalWindowService->getIndex($modalWindow);
        $modalWindowDto->title = $modalWindow->getTitle();
        $modalWindowDto->content = $modalWindow->getContent();

        return $modalWindowDto;
    }

    public function fromSuvey(Survey $survey): ModalWindowDto
    {
        $modalWindowDto = new ModalWindowDto();
        $modalWindowDto->index = $this->modalWindowService->getIndex($survey);
        $modalWindowDto->title = $survey->getTitle();
        $modalWindowDto->content = $survey->getContent();
        $modalWindowDto->url = $this->router->generate('survey', ['survey' => $survey->getId()]);
        $modalWindowDto->labelButton = 'Participer';

        return $modalWindowDto;
    }

    public function fromOrderHeader(OrderHeader $orderHeader): ModalWindowDto
    {
        $modalWindowDto = new ModalWindowDto();
        $modalWindowDto->index = $this->modalWindowService->getIndex($orderHeader);
        $modalWindowDto->title = 'Commande en cours';
        $modalWindowDto->content = $this->modalWindowOrderInProgress;
        $modalWindowDto->url = $this->router->generate('order_edit');
        $modalWindowDto->labelButton = 'Valider ma commande';

        return $modalWindowDto;
    }

    public function fromLicence(Licence $licence): ModalWindowDto
    {
        $modalWindowDto = new ModalWindowDto();
        $modalWindowDto->index = $this->modalWindowService->getIndex($licence);
        $modalWindowDto->title = 'Dossier d\'inscription en cours';
        $modalWindowDto->content = $this->modalWindowRegistrationInProgress;
        $modalWindowDto->url = $this->router->generate('user_registration_form', ['step' => 1]);
        $modalWindowDto->labelButton = 'Finaliser mon inscription';

        return $modalWindowDto;
    }

    public function fromEntities(array|Paginator|Collection $modalWindowEntities): array
    {
        $modalWindows = [];

        foreach ($modalWindowEntities as $modalWindowEntity) {
            if ($modalWindowEntity instanceof ModalWindow) {
                $modalWindows[] = $this->fromModalWindow($modalWindowEntity);
            }
            if ($modalWindowEntity instanceof Survey) {
                $modalWindows[] = $this->fromSuvey($modalWindowEntity);
            }
            if ($modalWindowEntity instanceof OrderHeader) {
                $modalWindows[] = $this->fromOrderHeader($modalWindowEntity);
            }
            if ($modalWindowEntity instanceof Licence) {
                $modalWindows[] = $this->fromLicence($modalWindowEntity);
            }
        }

        return $modalWindows;
    }
}
