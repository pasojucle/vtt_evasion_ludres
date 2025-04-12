<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\NotificationDto;
use App\Entity\BikeRide;
use App\Entity\Cluster;
use App\Entity\Enum\OrderStatusEnum;
use App\Entity\Licence;
use App\Entity\Notification;
use App\Entity\OrderHeader;
use App\Entity\Survey;
use App\Service\BikeRideService;
use App\Service\LogService;
use App\Service\MessageService;
use App\Service\NotificationService;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class NotificationDtoTransformer
{
    public function __construct(
        private readonly NotificationService $notificationService,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly MessageService $messageService,
        private readonly LogService $logService,
        private readonly BikeRideService $bikeRideService,
    ) {
    }

    public function fromNotification(Notification $notification): NotificationDto
    {
        $notificationDto = new NotificationDto();
        $notificationDto->index = $this->notificationService->getIndex($notification);
        $notificationDto->title = $notification->getTitle();
        $notificationDto->content = $notification->getContent();
        $notificationDto->form = $this->logService->getForm([
            'entityName' => 'Notification',
            'entityId' => $notification->getId(),
        ])->createView();
        $notificationDto->labelButton = 'J\'ai compris';
        $notificationDto->modalLink = $this->notificationService->getModalLinkFromEntity($notification);

        return $notificationDto;
    }

    private function fromSuvey(Survey $survey): NotificationDto
    {
        $notificationDto = new NotificationDto();
        $notificationDto->index = $this->notificationService->getIndex($survey);
        $notificationDto->title = $survey->getTitle();
        $notificationDto->content = $survey->getContent();
        $notificationDto->url = $this->urlGenerator->generate('survey', ['survey' => $survey->getId()]);
        $notificationDto->labelButton = 'Participer';
        $notificationDto->modalLink = $this->notificationService->getModalLinkFromEntity($survey);

        return $notificationDto;
    }

    private function fromCluster(Cluster $cluster): NotificationDto
    {
        $data = $this->notificationService->getClusterExport($cluster);

        return $this->fromArray($data);
    }

    private function fromOrderHeader(OrderHeader $orderHeader): NotificationDto
    {
        list($title, $url, $labelButton, $message) = match ($orderHeader->getStatus()) {
            OrderStatusEnum::IN_PROGRESS => ['Commande en cours', $this->urlGenerator->generate('order_edit'), 'Valider ma commande', 'MODAL_WINDOW_ORDER_IN_PROGRESS'],
            OrderStatusEnum::VALIDED => ['Commande validée', $this->urlGenerator->generate('order', ['orderHeader' => $orderHeader->getId()]), 'Finaliser ma commande', 'MODAL_WINDOW_ORDER_VALIDED'],
            default => ['Commande annulée', $this->urlGenerator->generate('order', ['orderHeader' => $orderHeader->getId()]), 'Voir ma commande', 'MODAL_WINDOW_ORDER_CANCELED'],
        };
        $notificationDto = new NotificationDto();
        $notificationDto->index = $this->notificationService->getIndex($orderHeader);
        $notificationDto->title = $title;
        $notificationDto->content = $this->messageService->getMessageByName($message);
        $notificationDto->url = $url;
        $notificationDto->labelButton = $labelButton;
        $notificationDto->modalLink = $this->notificationService->getModalLinkFromEntity($orderHeader);


        return $notificationDto;
    }

    private function fromLicence(Licence $licence): NotificationDto
    {
        $notificationDto = new NotificationDto();
        $notificationDto->index = $this->notificationService->getIndex($licence);
        $notificationDto->title = 'Dossier d\'inscription en cours';
        $notificationDto->content = $this->messageService->getMessageByName('MODAL_WINDOW_REGISTRATION_IN_PROGRESS');
        $notificationDto->url = $this->urlGenerator->generate('user_registration_form', ['step' => 1]);
        $notificationDto->labelButton = 'Finaliser mon inscription';
        $notificationDto->modalLink = $this->notificationService->getModalLinkFromEntity($licence);

        return $notificationDto;
    }

    private function fromBikeRide(BikeRide $bikeRide): NotificationDto
    {
        $notificationDto = new NotificationDto();
        $notificationDto->index = $this->notificationService->getIndex($bikeRide);
        $notificationDto->title = 'Nouvelle activité';
        $notificationDto->content = sprintf('%s - %s', $bikeRide->getTitle(), $this->bikeRideService->getPeriod($bikeRide));
        $notificationDto->url = $this->urlGenerator->generate('session_add', ['bikeRide' => $bikeRide->getId()]);
        $notificationDto->labelButton = 'Participer';
        $notificationDto->modalLink = $this->notificationService->getModalLinkFromEntity($bikeRide);

        return $notificationDto;
    }

    public function fromArray(array $data): NotificationDto
    {
        $notificationDto = new NotificationDto();
        $notificationDto->index = $this->notificationService->getIndex($data['index']);
        $notificationDto->title = $data['title'];
        $notificationDto->content = $data['content'];
        if (array_key_exists('route', $data)) {
            $notificationDto->url = $this->urlGenerator->generate($data['route'], $data['routeParams']);
        }
        if (array_key_exists('modalLink', $data)) {
            $notificationDto->modalLink = $data['modalLink'];
        }
        if (array_key_exists('toggle', $data)) {
            $notificationDto->toggle = $data['toggle'];
        }
        if (array_key_exists('url', $data)) {
            $notificationDto->url = $data['url'];
        }
        if (array_key_exists('target', $data)) {
            $notificationDto->target = $data['target'];
        }
        if (array_key_exists('labelBtn', $data)) {
            $notificationDto->labelButton = $data['labelBtn'];
        }
        if (array_key_exists('closeAfter', $data)) {
            $notificationDto->closeAfter = $data['closeAfter'];
        }
        
        return $notificationDto;
    }

    public function fromEntities(array|Paginator|Collection $notificationEntities): array
    {
        $notifications = [];

        foreach ($notificationEntities as $notificationEntity) {
            $notifications[] = $this->fromEntity($notificationEntity);
        }

        return $notifications;
    }

    public function fromEntity(array|Survey|Notification|Licence|OrderHeader|BikeRide|Cluster $entity): NotificationDto
    {
        return match (true) {
            $entity instanceof Notification => $this->fromNotification($entity),
            $entity instanceof Survey => $this->fromSuvey($entity),
            $entity instanceof OrderHeader => $this->fromOrderHeader($entity),
            $entity instanceof Licence => $this->fromLicence($entity),
            $entity instanceof BikeRide => $this->fromBikeRide($entity),
            $entity instanceof Cluster => $this->fromCluster($entity),
            default => $this->fromArray($entity)
        };
    }
}
