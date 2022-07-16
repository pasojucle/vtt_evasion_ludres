<?php

declare(strict_types=1);

namespace App\UseCase\ModalWindow;

use App\Entity\ModalWindow;
use App\Repository\ModalWindowRepository;
use App\Service\UserService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

class ShowModalWindow
{
    public function __construct(
        private RequestStack $requestStack,
        private Security $security,
        private UserService $userService,
        private ModalWindowRepository $modalWindowRepository
    ) {
    }

    public function execute(): ?ModalWindow
    {
        $session = $this->requestStack->getCurrentRequest()->getSession();
        $licenceNumber = $session->get('licence_number');
        $user = $this->security->getUser();
        $modal = null;
        if (null !== $user) {
            $user = $this->userService->convertToUser($user);
            if ($user->licenceNumber !== $licenceNumber) {
                $modalWindows = $this->modalWindowRepository->findLastByAge($user->member?->age);
                $session->set('licence_number', $user->licenceNumber);
            }
            if (!empty($modalWindows)) {
                $modal = $modalWindows[0];
            }
        }

        return $modal;
    }
}
