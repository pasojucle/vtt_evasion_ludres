<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\NotificationDto;
use App\Entity\Licence;
use App\Entity\Notification;
use App\Entity\OrderHeader;
use App\Entity\Survey;
use App\Service\MessageService;
use App\Service\NotificationService;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class NotificationDtoTransformer
{
    private ?string $notificationOrderInProgress;
    public ?string $notificationRegistrationInProgress;

    public function __construct(
        private NotificationService $notificationService,
        private UrlGeneratorInterface $router,
        private MessageService $messageService,
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

        return $notificationDto;
    }

    public function fromSuvey(Survey $survey): NotificationDto
    {
        $notificationDto = new NotificationDto();
        $notificationDto->index = $this->notificationService->getIndex($survey);
        $notificationDto->title = $survey->getTitle();
        $notificationDto->content = $survey->getContent();
        $notificationDto->url = $this->router->generate('survey', ['survey' => $survey->getId()]);
        $notificationDto->labelButton = 'Participer';

        return $notificationDto;
    }

    public function fromOrderHeader(OrderHeader $orderHeader): NotificationDto
    {
        $notificationDto = new NotificationDto();
        $notificationDto->index = $this->notificationService->getIndex($orderHeader);
        $notificationDto->title = 'Commande en cours';
        $notificationDto->content = $this->notificationOrderInProgress;
        $notificationDto->url = $this->router->generate('order_edit');
        $notificationDto->labelButton = 'Valider ma commande';

        return $notificationDto;
    }

    public function fromLicence(Licence $licence): NotificationDto
    {
        $notificationDto = new NotificationDto();
        $notificationDto->index = $this->notificationService->getIndex($licence);
        $notificationDto->title = 'Dossier d\'inscription en cours';
        $notificationDto->content = $this->notificationRegistrationInProgress;
        $notificationDto->url = $this->router->generate('user_registration_form', ['step' => 1]);
        $notificationDto->labelButton = 'Finaliser mon inscription';

        return $notificationDto;
    }

    public function fromArray(array $data): NotificationDto
    {
        $notificationDto = new NotificationDto();
        $notificationDto->index = $this->notificationService->getIndex($data['index']);
        $notificationDto->title = $data['title'];
        $notificationDto->content = $data['content'];
        if (array_key_exists('route', $data)) {
            $notificationDto->url = $this->router->generate($data['route'], $data['routeParams']);
            $notificationDto->labelButton = $data['labelBtn'];
        }

        return $notificationDto;
    }

    public function fromEntities(array|Paginator|Collection $notificationEntities): array
    {
        $notifications = [];

        foreach ($notificationEntities as $notificationEntity) {
            if ($notificationEntity instanceof Notification) {
                $notifications[] = $this->fromNotification($notificationEntity);
            }
            if ($notificationEntity instanceof Survey) {
                $notifications[] = $this->fromSuvey($notificationEntity);
            }
            if ($notificationEntity instanceof OrderHeader) {
                $notifications[] = $this->fromOrderHeader($notificationEntity);
            }
            if ($notificationEntity instanceof Licence) {
                $notifications[] = $this->fromLicence($notificationEntity);
            }
            if (is_array($notificationEntity)) {
                $notifications[] = $this->fromArray($notificationEntity);
            }
        }

        return $notifications;
    }
}
