<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Licence;
use App\Entity\ModalWindow;
use App\Entity\OrderHeader;
use App\Entity\Survey;
use App\Entity\User;
use DateTime;
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

    public function getIndex(Survey|OrderHeader|ModalWindow|Licence $entity)
    {
        /** @var User $user */
        $user = $this->security->getUser();
        dump($user);
        $id = (null !== $user) ? $user->getLicenceNumber() : 'PUBLIC_ACCESS';
        return $id . '-' . (new ReflectionClass($entity))->getShortName() . '-' . $entity->getId();
    }

    public function addToModalWindowShowOn(OrderHeader|Licence $entity): void
    {
        $session = $this->requestStack->getSession();
        $modalWindowShowOn = $session->get('modal_window_show_on');
        $modalWindowShowOn = (null !== $modalWindowShowOn) ? json_decode($modalWindowShowOn) : [];
        $modalWindowShowOn[] = $this->getIndex($entity);
        $session->set('modal_window_show_on', json_encode($modalWindowShowOn));
    }
}
