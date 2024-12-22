<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\NotificationDto;
use App\Entity\Licence;
use App\Entity\Notification;
use App\Entity\OrderHeader;
use App\Entity\Survey;
use App\Service\LogService;
use App\Service\MessageService;
use App\Service\NotificationService;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use ReflectionClass;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class NotificationDtoTransformer
{
    private ?string $notificationOrderInProgress;
    public ?string $notificationRegistrationInProgress;

    public function __construct(
        private readonly NotificationService $notificationService,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly MessageService $messageService,
        private readonly LogService $logService,
    ) {
        $this->notificationOrderInProgress = $this->messageService->getMessageByName('MODAL_WINDOW_ORDER_IN_PROGRESS');
        $this->notificationRegistrationInProgress = $this->messageService->getMessageByName('MODAL_WINDOW_REGISTRATION_IN_PROGRESS');
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
        $notificationDto->modalLink = $this->getModalLinkFromEntity($notification);

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
        $notificationDto->modalLink = $this->getModalLinkFromEntity($survey);

        return $notificationDto;
    }

    private function fromOrderHeader(OrderHeader $orderHeader): NotificationDto
    {
        $notificationDto = new NotificationDto();
        $notificationDto->index = $this->notificationService->getIndex($orderHeader);
        $notificationDto->title = 'Commande en cours';
        $notificationDto->content = $this->notificationOrderInProgress;
        $notificationDto->url = $this->urlGenerator->generate('order_edit');
        $notificationDto->labelButton = 'Valider ma commande';
        $notificationDto->modalLink = $this->getModalLinkFromEntity($orderHeader);

        return $notificationDto;
    }

    private function fromLicence(Licence $licence): NotificationDto
    {
        $notificationDto = new NotificationDto();
        $notificationDto->index = $this->notificationService->getIndex($licence);
        $notificationDto->title = 'Dossier d\'inscription en cours';
        $notificationDto->content = $this->notificationRegistrationInProgress;
        $notificationDto->url = $this->urlGenerator->generate('user_registration_form', ['step' => 1]);
        $notificationDto->labelButton = 'Finaliser mon inscription';
        $notificationDto->modalLink = $this->getModalLinkFromEntity($licence);

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
            $notificationDto->target = $data['target'];
        }

        if (array_key_exists('labelBtn', $data)) {
            $notificationDto->labelButton = $data['labelBtn'];
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

    public function fromEntity(array|Survey|Notification|Licence|OrderHeader $entity): NotificationDto
    {
        return match (true) {
            $entity instanceof Notification => $this->fromNotification($entity),
            $entity instanceof Survey => $this->fromSuvey($entity),
            $entity instanceof OrderHeader => $this->fromOrderHeader($entity),
            $entity instanceof Licence => $this->fromLicence($entity),
            default => $this->fromArray($entity)
        };
    }

    private function getModalLinkFromEntity(string|Survey|Notification|Licence|OrderHeader $entity): string
    {
        return $this->urlGenerator->generate('notification_show', [
            'entityName' => (new ReflectionClass($entity))->getShortName(),
            'entityId' => $entity->getId(),
        ]);
    }
}
