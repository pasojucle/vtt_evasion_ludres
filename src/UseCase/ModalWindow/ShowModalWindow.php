<?php

declare(strict_types=1);

namespace App\UseCase\ModalWindow;


use App\Service\UserService;
use App\Repository\SurveyRepository;
use App\Repository\ModalWindowRepository;
use App\Repository\OrderHeaderRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use App\ViewModel\ModalWindow\ModalWindowsPresenter;
use App\ViewModel\ModalWindow\ModalWindowViewModel;

class ShowModalWindow
{
    public function __construct(
        private RequestStack $requestStack,
        private Security $security,
        private UserService $userService,
        private ModalWindowRepository $modalWindowRepository,
        private SurveyRepository $surveyRepository,
        private OrderHeaderRepository $orderHeaderRepository,
        private ModalWindowsPresenter $modalWindowsPresenter
    ) {
    }

    public function execute(): ?ModalWindowViewModel
    {
        $session = $this->requestStack->getCurrentRequest()->getSession();
        $modalWindowShowOn = (null !== $session->get('modal_window_show_on'))
            ? json_decode($session->get('modal_window_show_on'), true)
            : [];
        $user = $this->security->getUser();

        if (null !== $user) {
            $user = $this->userService->convertToUser($user);
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
