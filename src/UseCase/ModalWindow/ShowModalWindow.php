<?php

declare(strict_types=1);

namespace App\UseCase\ModalWindow;

use App\Dto\DtoTransformer\ModalWindowDtoTransformer;
use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Dto\ModalWindowDto;
use App\Entity\Licence;
use App\Entity\User;
use App\Repository\ModalWindowRepository;
use App\Repository\OrderHeaderRepository;
use App\Repository\SurveyRepository;
use App\Service\ParameterService;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;

class ShowModalWindow
{
    private SessionInterface $session;
    public function __construct(
        private RequestStack $requestStack,
        private Security $security,
        private ModalWindowRepository $modalWindowRepository,
        private SurveyRepository $surveyRepository,
        private OrderHeaderRepository $orderHeaderRepository,
        private ModalWindowDtoTransformer $modalWindowDtoTransformer,
        private UserDtoTransformer $userDtoTransformer,
        private RouterInterface $router,
        private ParameterService $parameterService,
    ) {
    }

    public function execute(): ?ModalWindowDto
    {
        $this->session = $this->requestStack->getCurrentRequest()->getSession();
        $modalWindowShowOn = (null !== $this->session->get('modal_window_showed'))
            ? json_decode($this->session->get('modal_window_showed'), true)
            : [];
        /** @var User $user */
        $user = $this->security->getUser();

        $modalWindows = (null !== $user)
            ? $this->getUserModalWindows($user)
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

    private function getUserModalWindows(User $user): array
    {
        $userDto = $this->userDtoTransformer->fromEntity($user);

        if (!$userDto->lastLicence->isActive) {
            return [];
        }

        $modalWindowsToShowJson = $this->session->get('modal_windows_to_show');
        $modalWindowsToShow = ($modalWindowsToShowJson) ? json_decode($modalWindowsToShowJson, true) : [];

        $modalWindows = $this->modalWindowRepository->findByAge($userDto->member?->age);
        $modalWindowsToShow = array_merge($modalWindowsToShow, $modalWindows);
        $surveys = $this->surveyRepository->findActiveAndWithoutResponse($user);
        $modalWindowsToShow = array_merge($modalWindowsToShow, $surveys);
        $orderHeaderToValidate = $this->orderHeaderRepository->findOneOrderNotEmpty($user);

        if (null !== $orderHeaderToValidate) {
            $modalWindowsToShow[] = $orderHeaderToValidate;
        }

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

        if (Licence::STATUS_IN_PROCESSING === $userDto->lastLicence?->status && !str_contains($route['_route'], 'registration_form')) {
            $modalWindowsToShow[] = $user->getLastLicence();
        }

        $season = $this->session->get('currentSeason');

        if ($this->parameterService->getParameterByName('NEW_SEASON_RE_REGISTRATION_ENABLED') && Licence::STATUS_WAITING_RENEW === $userDto->lastLicence->status) {
            $modalWindowsToShow[] = [
                'index' => 'NEW_SEASON_RE_REGISTRATION_ENABLED',
                'title' => sprintf('Inscription à la saison %s', $season),
                'content' => $this->parameterService->getParameterByName('NEW_SEASON_RE_REGISTRATION_ENABLED_MESSAGE'),
                'route' => 'user_registration_form',
                'routeParams' => ['step' => 1],
                'labelBtn' => 'S\'incrire'
            ];
        }

        return $modalWindowsToShow;
    }
}
