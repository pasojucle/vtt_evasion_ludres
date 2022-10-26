<?php

declare(strict_types=1);

namespace App\UseCase\ModalWindow;

use App\Entity\User;
use App\Repository\ModalWindowRepository;
use App\Repository\OrderHeaderRepository;
use App\Repository\SurveyRepository;
use App\ViewModel\ModalWindow\ModalWindowsPresenter;
use App\ViewModel\ModalWindow\ModalWindowViewModel;
use App\ViewModel\UserPresenter;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

class ShowModalWindow
{
    public function __construct(
        private RequestStack $requestStack,
        private Security $security,
        private ModalWindowRepository $modalWindowRepository,
        private SurveyRepository $surveyRepository,
        private OrderHeaderRepository $orderHeaderRepository,
        private ModalWindowsPresenter $modalWindowsPresenter,
        private UserPresenter $userPresenter
    ) {
    }

    public function execute(): ?ModalWindowViewModel
    {
        $session = $this->requestStack->getCurrentRequest()->getSession();
        $modalWindowShowOn = (null !== $session->get('modal_window_show_on'))
            ? json_decode($session->get('modal_window_show_on'), true)
            : [];
            
        /** @var User $user */
        $user = $this->security->getUser();

        if (null !== $user) {
            $this->userPresenter->present($user);
            $user = $this->userPresenter->viewModel();

            $modalWindows = $this->modalWindowRepository->findLastByAge($user->member?->age);
            $surveys = $this->surveyRepository->findActiveAndWithoutResponse($user->entity);
            $modalWindows = array_merge($modalWindows, $surveys);
            $orderHeaderToValidate = $this->orderHeaderRepository->findOneOrderNotEmpty($user->entity);

            if (null !== $orderHeaderToValidate) {
                $modalWindows = array_merge($modalWindows, [$orderHeaderToValidate]);
            }
            
            $this->modalWindowsPresenter->present($modalWindows);


            foreach ($this->modalWindowsPresenter->viewModel()->modalWindows as $modalWindow) {
                if (!in_array($modalWindow->index, $modalWindowShowOn)) {
                    $modalWindowShowOn[] = $modalWindow->index;
                    $session->set('modal_window_show_on', json_encode($modalWindowShowOn));
                    return $modalWindow;
                }
            }
        }

        return null;
    }
}
