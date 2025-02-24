<?php

declare(strict_types=1);

namespace App\UseCase\Notification;

use App\Dto\DtoTransformer\NotificationDtoTransformer;
use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Dto\UserDto;
use App\Entity\BikeRide;
use App\Entity\Licence;
use App\Entity\Notification;
use App\Entity\OrderHeader;
use App\Entity\Survey;
use App\Entity\User;
use App\Repository\BikeRideRepository;
use App\Repository\NotificationRepository;
use App\Repository\OrderHeaderRepository;
use App\Repository\SurveyRepository;
use App\Service\NotificationService;
use App\Service\ParameterService;
use App\Service\RouterService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class GetList
{
    private SessionInterface $session;

    private ?User $user;
    private UserDto $userDto;
    private array $routeInfos;

    public function __construct(
        private readonly RequestStack $requestStack,
        protected Security $security,
        private readonly NotificationRepository $notificationRepository,
        private readonly SurveyRepository $surveyRepository,
        private readonly BikeRideRepository $bikeRideRepository,
        private readonly OrderHeaderRepository $orderHeaderRepository,
        private readonly NotificationDtoTransformer $notificationDtoTransformer,
        private readonly UserDtoTransformer $userDtoTransformer,
        private readonly RouterService $routerService,
        private readonly ParameterService $parameterService,
        private readonly NotificationService $notificationService,
    ) {
        /** @var ?User $user */
        $user = $security->getUser();
        $this->user = $user;
    }

    public function execute(): array
    {
        $this->session = $this->requestStack->getCurrentRequest()->getSession();
        $this->routeInfos = $this->routerService->getRouteInfos();
        
        $notificationsConsumed = $this->notificationService->sessionToArray('notifications_consumed');
        $notification = $this->fromReferer();
        if ($notification) {
            return [$this->notificationDtoTransformer->fromEntity($notification), []];
        }

        if (null === $this->user) {
            return $this->getPublicNotifications($notificationsConsumed);
        }

        return $this->getUserNotifications($notificationsConsumed);
    }

    private function getPublicNotifications(array $notificationsConsumed): array
    {
        return [$this->getPublicNotification($notificationsConsumed), []];
    }

    private function getPublicNotification(array $notificationsConsumed): ?Notification
    {
        $notifications = $this->notificationRepository->findPublic();
        return $this->getNotification($notificationsConsumed, $notifications);
    }

    private function getNotification(array $notificationsConsumed, array $notifications): null|array|Survey|Notification|Licence|OrderHeader|BikeRide
    {
        if (!empty($notifications)) {
            foreach ($notifications as $notification) {
                $notificationIndex = $this->notificationService->getIndex($notification);
                if (!in_array($notificationIndex, $notificationsConsumed)) {
                    $notificationsConsumed[] = $notificationIndex;
                    $this->session->set('notifications_consumed', json_encode($notificationsConsumed));
                    return $notification;
                }
            }
        }

        return null;
    }

    private function getUserNotifications(array $notificationsConsumed): array
    {
        if ($this->routeInfos['_route'] && str_contains($this->routeInfos['_route'], 'admin')) {
            return [null, []];
        }

        $this->userDto = $this->userDtoTransformer->fromEntity($this->user);
        if (!$this->userDto->lastLicence->isActive) {
            return $this->getPublicNotifications($notificationsConsumed);
        }
        $notifications = [];
        $this->addBikeRide($notifications);
        $this->addSurveys($notifications);
        $this->addSurveysChanged($notifications);
        $this->addNewOrderToValidate($notifications);
        $this->addOrderValidatedOrCanceled($notifications);
        $this->addRegistationInProgress($notifications);
        $this->addNewSeasonReRgistrationEnabled($notifications);

        $modalNotifications = $this->notificationRepository->findByUser($this->user, $this->userDto->member->age);
        if (empty($modalNotifications) && empty($notificationsConsumed)) {
            $total = count($notifications);
            $notification = (1 < $total)
                ? $this->notificationService->getPendingNotifications($total)
                : array_shift($notifications);
            if ($notification) {
                $modalNotifications[] = $notification;
            }
        }
        $modalNotification = $this->getNotification($notificationsConsumed, $modalNotifications);

        return [
            ($modalNotification) ? $this->notificationDtoTransformer->fromEntity($modalNotification) : null,
            $this->notificationDtoTransformer->fromEntities($notifications)
        ];
    }

    private function addSurveys(array &$notifications): void
    {
        $surveys = $this->surveyRepository->findActiveAndWithoutResponse($this->user);
        foreach ($surveys as $survey) {
            if ('survey' !== $this->routeInfos['_route'] || $survey->getId() !== (int) $this->routeInfos['survey']) {
                $notifications[] = $survey;
            }
        };
    }

    private function addBikeRide(array &$notifications): void
    {
        $bikeRides = $this->bikeRideRepository->findNotifiable($this->user);
        foreach ($bikeRides as $bikeRide) {
            $notifications[] = $bikeRide;
        };
    }

    private function addRegistationInProgress(array &$notifications): void
    {
        if (in_array($this->routeInfos['_route'], ['registration_form', 'user_registration_form'])) {
            return;
        };

        if (Licence::STATUS_IN_PROCESSING === $this->userDto->lastLicence?->status) {
            $notifications[] = $this->user->getLastLicence();
        }
    }

    private function addNewOrderToValidate(array &$notifications): void
    {
        if (in_array($this->routeInfos['_route'], ['order_edit', 'products'])) {
            return;
        };
        $orderHeaderToValidate = $this->orderHeaderRepository->findOneOrderNotEmpty($this->user);
        if (null !== $orderHeaderToValidate) {
            $notifications[] = $orderHeaderToValidate;
        }
    }

    private function addOrderValidatedOrCanceled(array &$notifications): void
    {
        $orderHeadersValidated = $this->orderHeaderRepository->findValidedOrCanceled($this->user);
        foreach ($orderHeadersValidated as $orderHeader) {
            $notifications[] = $orderHeader;
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
            $notifications[] = $this->notificationService->getSurveyChanged($survey);
        }
    }

    private function fromReferer(): ?array
    {
        $refererNotification = $this->notificationService->sessionToArray('notification');

        if (!$refererNotification) {
            return null;
        }
        $this->notificationService->clear();
        return match ($refererNotification['entity']) {
            'Survey' => $this->notificationService->setSurveyChanged($refererNotification['entityId']),
            'Session' => $this->notificationService->getSessionRegistred($refererNotification['entityId']),
            default => null,
        };
    }
}
