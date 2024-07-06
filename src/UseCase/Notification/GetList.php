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
use App\Service\NotificationService;
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
        private readonly NotificationService $notificationService,
    ) {
        /** @var ?User $user */
        $user = $security->getUser();
        $this->user = $user;
    }

    public function execute(): array
    {
        $this->session = $this->requestStack->getCurrentRequest()->getSession();
        $notificationShowOn = (null !== $this->session->get('notification_showed'))
        ? json_decode($this->session->get('notification_showed'), true)
        : [];
        if (null === $this->user) {
            return $this->getPublicNotifications($notificationShowOn);
        }

        return $this->getUserNotifications($notificationShowOn);
    }

    private function getPublicNotifications(array $notificationShowOn): array
    {
        return [$this->getPublicNotification($notificationShowOn), []];
    }

    private function getPublicNotification(array $notificationShowOn): ?Notification
    {
        $notifications = $this->notificationRepository->findPublic();
        return $this->getNotification($notificationShowOn, $notifications);
    }

    private function getNotification(array $notificationShowOn, array $notifications): ?Notification
    {
        if (!empty($notifications)) {
            foreach ($notifications as $notification) {
                $notificationIndex = $this->notificationService->getIndex($notification);
                if (!in_array($notificationIndex, $notificationShowOn)) {
                    $notificationShowOn[] = $notificationIndex;
                    $this->session->set('notification_showed', json_encode($notificationShowOn));
                    return $notification;
                }
            }
        }

        return null;
    }

    private function getUserNotifications(array $notificationShowOn): array
    {
        $this->userDto = $this->userDtoTransformer->fromEntity($this->user);
        if (!$this->userDto->lastLicence->isActive) {
            return $this->getPublicNotifications($notificationShowOn);
        }
        
        $notifications = $this->notificationRepository->findByUser($this->user, $this->userDto->member->age);
        $modalNotification = $this->getNotification($notificationShowOn, $notifications);

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

    private function addSurveys(array &$notifications): void
    {
        $surveys = $this->surveyRepository->findActiveAndWithoutResponse($this->user);
        foreach ($surveys as $survey) {
            if ('survey' !== $this->getReferer()['_route'] || $survey->getId() !== (int) $this->getReferer()['survey']) {
                $notifications[] = $survey;
            }
        };
    }

    private function addRegistationInProgress(array &$notifications): void
    {
        if (in_array($this->getReferer()['_route'], ['registration_form', 'user_registration_form'])) {
            return;
        };

        if (Licence::STATUS_IN_PROCESSING === $this->userDto->lastLicence?->status) {
            $notifications[] = $this->user->getLastLicence();
        }
    }

    private function addNewOrderToValidate(array &$notifications): void
    {
        if (in_array($this->getReferer()['_route'], ['order_edit', 'products'])) {
            return;
        };
        $orderHeaderToValidate = $this->orderHeaderRepository->findOneOrderNotEmpty($this->user);
        if (null !== $orderHeaderToValidate) {
            $notifications[] = $orderHeaderToValidate;
        }
    }

    private function addNewSeasonReRgistrationEnabled(array &$notifications): void
    {
        if ($this->parameterService->getParameterByName('NEW_SEASON_RE_REGISTRATION_ENABLED') && Licence::STATUS_WAITING_RENEW === $this->userDto->lastLicence->status) {
            $notifications[] = $this->notificationService->getNewSeasonReRegistration();
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

    private function getReferer(): array
    {
        $search = [
            $this->requestStack->getCurrentRequest()->getScheme(),
            '://',
            $this->requestStack->getCurrentRequest()->headers->get('host'),
        ];
        $referer = str_replace($search, '', $this->requestStack->getCurrentRequest()->headers->get('referer'));
        try {
            $referer = $this->router->match($referer);
        } catch (Exception) {
            return ['_route' => null];
        }

        return $referer;
    }
}
