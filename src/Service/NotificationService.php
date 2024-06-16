<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Licence;
use App\Entity\Notification;
use App\Entity\OrderHeader;
use App\Entity\Survey;
use App\Entity\User;
use DateTime;
use DateTimeImmutable;
use ReflectionClass;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

class NotificationService
{
    public function __construct(
        private Security $security,
        private RequestStack $requestStack
    ) {
    }

    public function getIndex(Survey|OrderHeader|Notification|Licence|string $entity)
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $id = (null !== $user) ? $user->getLicenceNumber() : 'PUBLIC_ACCESS';
        return (is_string($entity))
            ? $id . '-' . $entity
            : $id . '-' . (new ReflectionClass($entity))->getShortName() . '-' . $entity->getId();
    }

    public function addToNotificationShowed(OrderHeader|Licence $entity): void
    {
        $session = $this->requestStack->getSession();
        $notificationShowOn = $session->get('notification_showed');
        $notificationShowOn = (null !== $notificationShowOn) ? json_decode($notificationShowOn) : [];
        $notificationShowOn[] = $this->getIndex($entity);
        $session->set('notification_showed', json_encode($notificationShowOn));
    }

    public function addToNotification(string $title, string $content): void
    {
        $session = $this->requestStack->getSession();
        $notificationsToShowJson = $session->get('notifications_to_show');
        $notifications = ($notificationsToShowJson) ? json_decode($notificationsToShowJson, true) : [];
        $notifications[] = [
            'index' => (string) (new DateTimeImmutable())->getTimestamp(),
            'title' => $title,
            'content' => $content,
        ];
        $session->set('notifications_to_show', json_encode($notifications));
    }
}
