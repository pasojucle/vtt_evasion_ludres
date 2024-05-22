<?php

declare(strict_types=1);

namespace App\UseCase\ModalWindow;

use App\Dto\DtoTransformer\ModalWindowDtoTransformer;
use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Dto\ModalWindowDto;
use App\Dto\UserDto;
use App\Entity\Licence;
use App\Entity\Survey;
use App\Entity\User;
use App\Repository\ModalWindowRepository;
use App\Repository\OrderHeaderRepository;
use App\Repository\SurveyRepository;
use App\Service\MessageService;
use App\Service\ParameterService;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;

class ShowModalWindow
{
    private SessionInterface $session;
    private array $modalWindowsToShow;
    private ?User $user;
    private UserDto $userDto;

    public function __construct(
        private readonly RequestStack $requestStack,
        protected Security $security,
        private readonly ModalWindowRepository $modalWindowRepository,
        private readonly SurveyRepository $surveyRepository,
        private readonly OrderHeaderRepository $orderHeaderRepository,
        private readonly ModalWindowDtoTransformer $modalWindowDtoTransformer,
        private readonly UserDtoTransformer $userDtoTransformer,
        private readonly RouterInterface $router,
        private readonly ParameterService $parameterService,
        private readonly MessageService $messageService,
    ) {
        /** @var ?User $user */
        $user = $security->getUser();
        $this->user = $user;
    }

    public function execute(): ?ModalWindowDto
    {
        $this->session = $this->requestStack->getCurrentRequest()->getSession();
        $modalWindowShowOn = (null !== $this->session->get('modal_window_showed'))
            ? json_decode($this->session->get('modal_window_showed'), true)
            : [];

        $modalWindows = (null !== $this->user)
            ? $this->getUserModalWindows()
            : $this->modalWindowRepository->findPublic();

        if (!empty($modalWindows)) {
            $modalWidowSDto = $this->modalWindowDtoTransformer->fromEntities($modalWindows);

            foreach ($modalWidowSDto as $modalWindow) {
                if (!in_array($modalWindow->index, $modalWindowShowOn)) {
                    $modalWindowShowOn[] = $modalWindow->index;
                    $this->session->set('modal_window_showed', json_encode($modalWindowShowOn));
                    return $modalWindow;
                }
            }
        }

        return null;
    }

    private function getUserModalWindows(): array
    {
        $this->userDto = $this->userDtoTransformer->fromEntity($this->user);
        if (!$this->userDto->lastLicence->isActive) {
            return [];
        }
        
        $modalWindowsToShowJson = $this->session->get('modal_windows_to_show');
        $this->modalWindowsToShow = ($modalWindowsToShowJson) ? json_decode($modalWindowsToShowJson, true) : [];
        $this->addModalWindows();
        $this->addSurveys();
        $this->addSurveysChanged();
        $this->addNewOrderToValidate();
        $this->addRegistationInProgress();
        $this->addNewSeasonReRgistrationEnabled();

        return $this->modalWindowsToShow;
    }

    private function addModalWindows(): void
    {
        $modalWindows = $this->modalWindowRepository->findByAge($this->userDto->member?->age);
        $this->modalWindowsToShow = array_merge($this->modalWindowsToShow, $modalWindows);
    }

    private function addSurveys(): void
    {
        $surveys = $this->surveyRepository->findActiveAndWithoutResponse($this->user);
        $this->modalWindowsToShow = array_merge($this->modalWindowsToShow, $surveys);
    }

    private function addRegistationInProgress(): void
    {
        $search = [
            $this->requestStack->getCurrentRequest()->getScheme(),
            '://',
            $this->requestStack->getCurrentRequest()->headers->get('host'),
        ];
        $referer = str_replace($search, '', $this->requestStack->getCurrentRequest()->headers->get('referer'));
        try {
            $route = $this->router->match($referer);
        } catch (Exception) {
            $route = null;
        }

        if (Licence::STATUS_IN_PROCESSING === $this->userDto->lastLicence?->status && !str_contains($route['_route'], 'registration_form')) {
            $this->modalWindowsToShow[] = $this->user->getLastLicence();
        }
    }

    private function addNewOrderToValidate(): void
    {
        $orderHeaderToValidate = $this->orderHeaderRepository->findOneOrderNotEmpty($this->user);

        if (null !== $orderHeaderToValidate) {
            $this->modalWindowsToShow[] = $orderHeaderToValidate;
        }
    }

    private function addNewSeasonReRgistrationEnabled(): void
    {
        $season = $this->session->get('currentSeason');
        if ($this->parameterService->getParameterByName('NEW_SEASON_RE_REGISTRATION_ENABLED') && Licence::STATUS_WAITING_RENEW === $this->userDto->lastLicence->status) {
            $this->modalWindowsToShow[] = [
                'index' => 'NEW_SEASON_RE_REGISTRATION_ENABLED',
                'title' => sprintf('Inscription à la saison %s', $season),
                'content' => $this->messageService->getMessageByName('NEW_SEASON_RE_REGISTRATION_ENABLED_MESSAGE'),
                'route' => 'user_registration_form',
                'routeParams' => ['step' => 1],
                'labelBtn' => 'S\'incrire'
            ];
        }
    }

    private function addSurveysChanged(): void
    {
        $surveysChanged = $this->surveyRepository->findActiveChangedUser($this->user);
        /** @var Survey $survey */
        foreach ($surveysChanged as $survey) {
            $this->modalWindowsToShow[] = [
                'index' => sprintf('SURVEY_CHANGED_%s', $survey->getId()),
                'title' => sprintf('Modification du sondage %s', $survey->getTitle()),
                'content' => str_replace('{{ sondage }}', $survey->getTitle(), $this->messageService->getMessageByName('SURVEY_CHANGED_MESSAGE')),
                'route' => 'survey',
                'routeParams' => ['survey' => $survey->getId()],
                'labelBtn' => 'Consulter'
            ];
        }
    }
}
