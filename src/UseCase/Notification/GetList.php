<?php

declare(strict_types=1);

namespace App\UseCase\Notification;

use App\Dto\DtoTransformer\NotificationDtoTransformer;
use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Dto\UserDto;
use App\Entity\Licence;
use App\Entity\Notification;
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

    public function execute(): array
    {
        $this->session = $this->requestStack->getCurrentRequest()->getSession();
        if (null === $this->user) {
            return $this->getPublicNotifications();
        }

        return $this->getUserNotifications();
    }

    private function getPublicNotifications(): array
    {
        return [$this->getPublicNotification(), []];
    }

    private function getPublicNotification(): ?Notification
    {
        $notificationShowOn = (null !== $this->session->get('notification_showed'))
            ? json_decode($this->session->get('notification_showed'), true)
            : [];

        $notifications = $this->notificationRepository->findPublic();
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
            return $this->getPublicNotifications();
        }
        
        $modalNotifications = $this->getModalNotifications();
        $modalNotification = array_shift($modalNotifications);
        $notifications = $modalNotifications;
        $this->addSurveys($notifications);
        // $this->addSurveysChanged();
        $this->addNewOrderToValidate($notifications);
        $this->addRegistationInProgress($notifications);
        $this->addNewSeasonReRgistrationEnabled($notifications);

        return [
            ($modalNotification) ? $this->notificationDtoTransformer->fromNotification($modalNotification) : null,
            $this->notificationDtoTransformer->fromEntities($notifications)
        ];
    }

    private function getModalNotifications(): array
    {
        return $this->notificationRepository->findByUser($this->user, $this->userDto->member->age);
    }

    private function addSurveys(array &$notifications): void
    {
        $surveys = $this->surveyRepository->findActiveAndWithoutResponse($this->user);
        $notifications = array_merge($notifications, $surveys);
    }

    private function addRegistationInProgress(array &$notifications): void
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
            $notifications[] = $this->user->getLastLicence();
        }
    }

    private function addNewOrderToValidate(array &$notifications): void
    {
        $orderHeaderToValidate = $this->orderHeaderRepository->findOneOrderNotEmpty($this->user);

        if (null !== $orderHeaderToValidate) {
            $notifications[] = $orderHeaderToValidate;
        }
    }

    private function addNewSeasonReRgistrationEnabled(array &$notifications): void
    {
        $season = $this->session->get('currentSeason');
        if ($this->parameterService->getParameterByName('NEW_SEASON_RE_REGISTRATION_ENABLED') && Licence::STATUS_WAITING_RENEW === $this->userDto->lastLicence->status) {
            $notifications[] = [
                'index' => 'NEW_SEASON_RE_REGISTRATION_ENABLED',
                'title' => sprintf('Inscription à la saison %s', $season),
                'content' => $this->messageService->getMessageByName('NEW_SEASON_RE_REGISTRATION_ENABLED_MESSAGE'),
                'route' => 'user_registration_form',
                'routeParams' => ['step' => 1],
                'labelBtn' => 'S\'incrire'
            ];
        }
    }

    private function addSurveysChanged(array &$notifications): void
    {
        $surveysChanged = $this->surveyRepository->findActiveChangedUser($this->user);
        /** @var Survey $survey */
        foreach ($surveysChanged as $survey) {
            $notifications[] = [
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
