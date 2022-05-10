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
        $modalWindow = $session->get('show_modal_window');
        $user = $this->security->getUser();
        $modal = null;
        if (null !== $user) {
            $user = $this->userService->convertToUser($user);
            if ($user->licenceNumber !== $modalWindow) {
                $modalWindows = $this->modalWindowRepository->findLastByAge($user->member?->age);
                $session->set('show_modal_window', $user->licenceNumber);
            }
            if (!empty($modalWindows)) {
                $modal = $modalWindows[0];
            }
        }

        return $modal;
    }
}
