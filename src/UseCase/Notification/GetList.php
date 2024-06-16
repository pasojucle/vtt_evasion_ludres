<?php

declare(strict_types=1);

namespace App\UseCase\Notification;

use App\Dto\DtoTransformer\NotificationDtoTransformer;
use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Dto\NotificationDto;
use App\Dto\UserDto;
use App\Entity\Licence;
use App\Entity\Survey;
use App\Entity\User;
use App\Repository\NotificationRepository;
use App\Repository\OrderHeaderRepository;
use App\Repository\SurveyRepository;
use App\Service\MessageService;
use App\Service\ParameterService;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;

class GetList
{
    private SessionInterface $session;
    private array $allNotifications;
    private ?User $user;
    private UserDto $userDto;

    public function __construct(
        private readonly RequestStack $requestStack,
        protected Security $security,
        private readonly NotificationRepository $notificationRepository,
        private readonly SurveyRepository $surveyRepository,
        private readonly OrderHeaderRepository $orderHeaderRepository,
        private readonly NotificationDtoTransformer $notificationDtoTransformer,
        private readonly UserDtoTransformer $userDtoTransformer,
        private readonly RouterInterface $router,
        private readonly ParameterService $parameterService,
        private readonly MessageService $messageService,
    ) {
        /** @var ?User $user */
        $user = $security->getUser();
        $this->user = $user;
    }

    public function execute(): ?NotificationDto
    {
        $this->session = $this->requestStack->getCurrentRequest()->getSession();
        $notificationShowOn = (null !== $this->session->get('notification_showed'))
            ? json_decode($this->session->get('notification_showed'), true)
            : [];

        $notifications = (null !== $this->user)
            ? $this->getUserNotifications()
            : $this->notificationRepository->findPublic();

        if (!empty($notifications)) {
            $modalWidowSDto = $this->notificationDtoTransformer->fromEntities($notifications);

            foreach ($modalWidowSDto as $notification) {
                if (!in_array($notification->index, $notificationShowOn)) {
                    $notificationShowOn[] = $notification->index;
                    $this->session->set('notification_showed', json_encode($notificationShowOn));
                    return $notification;
                }
            }
        }

        return null;
    }

    private function getUserNotifications(): array
    {
        $this->userDto = $this->userDtoTransformer->fromEntity($this->user);
        if (!$this->userDto->lastLicence->isActive) {
            return [];
        }
        
        $allNotificationsJson = $this->session->get('notifications_to_show');
        $this->allNotifications = ($allNotificationsJson) ? json_decode($allNotificationsJson, true) : [];
        $this->addNotifications();
        $this->addSurveys();
        // $this->addSurveysChanged();
        $this->addNewOrderToValidate();
        $this->addRegistationInProgress();
        $this->addNewSeasonReRgistrationEnabled();
        return $this->allNotifications;
    }

    private function addNotifications(): void
    {
        $notifications = $this->notificationRepository->findByAge($this->userDto->member?->age);
        $this->allNotifications = array_merge($this->allNotifications, $notifications);
    }

    private function addSurveys(): void
    {
        $surveys = $this->surveyRepository->findActiveAndWithoutResponse($this->user);
        $this->allNotifications = array_merge($this->allNotifications, $surveys);
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
            $this->allNotifications[] = $this->user->getLastLicence();
        }
    }

    private function addNewOrderToValidate(): void
    {
        $orderHeaderToValidate = $this->orderHeaderRepository->findOneOrderNotEmpty($this->user);

        if (null !== $orderHeaderToValidate) {
            $this->allNotifications[] = $orderHeaderToValidate;
        }
    }

    private function addNewSeasonReRgistrationEnabled(): void
    {
        $season = $this->session->get('currentSeason');
        if ($this->parameterService->getParameterByName('NEW_SEASON_RE_REGISTRATION_ENABLED') && Licence::STATUS_WAITING_RENEW === $this->userDto->lastLicence->status) {
            $this->allNotifications[] = [
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
            $this->allNotifications[] = [
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
