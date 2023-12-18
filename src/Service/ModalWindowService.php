<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Licence;
use App\Entity\ModalWindow;
use App\Entity\OrderHeader;
use App\Entity\Survey;
use App\Entity\User;
use DateTime;
use DateTimeImmutable;
use ReflectionClass;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

class ModalWindowService
{
    public function __construct(
        private Security $security,
        private RequestStack $requestStack
    ) {
    }

    public function getIndex(Survey|OrderHeader|ModalWindow|Licence|string $entity)
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $id = (null !== $user) ? $user->getLicenceNumber() : 'PUBLIC_ACCESS';
        return (is_string($entity))
            ? $id . '-' . $entity
            : $id . '-' . (new ReflectionClass($entity))->getShortName() . '-' . $entity->getId();
    }

    public function addToModalWindowShowed(OrderHeader|Licence $entity): void
    {
        $session = $this->requestStack->getSession();
        $modalWindowShowOn = $session->get('modal_window_showed');
        $modalWindowShowOn = (null !== $modalWindowShowOn) ? json_decode($modalWindowShowOn) : [];
        $modalWindowShowOn[] = $this->getIndex($entity);
        $session->set('modal_window_showed', json_encode($modalWindowShowOn));
    }

    public function addToModalWindow(string $title, string $content): void
    {
        $session = $this->requestStack->getSession();
        $modalWindowsToShowJson = $session->get('modal_windows_to_show');
        $modalWindows = ($modalWindowsToShowJson) ? json_decode($modalWindowsToShowJson, true) : [];
        $modalWindows[] = [
            'index' => (string) (new DateTimeImmutable())->getTimestamp(),
            'title' => $title,
            'content' => $content,
        ];
        $session->set('modal_windows_to_show', json_encode($modalWindows));
    }
}
