<?php

declare(strict_types=1);

namespace App\UseCase\ModalWindow;

use ReflectionClass;
use App\Entity\ModalWindow;
use App\Entity\Survey;
use App\Service\UserService;
use App\Repository\SurveyRepository;
use App\Repository\ModalWindowRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\RequestStack;

class ShowModalWindow
{
    public function __construct(
        private RequestStack $requestStack,
        private Security $security,
        private UserService $userService,
        private ModalWindowRepository $modalWindowRepository,
        private SurveyRepository $surveyRepository
    ) {
    }

    public function execute(): ModalWindow|Survey|null
    {
        $session = $this->requestStack->getCurrentRequest()->getSession();
        $modalWindowShowOn = (null !== $session->get('modal_window_show_on'))
            ?  json_decode($session->get('modal_window_show_on'), true)
            : [];
        $user = $this->security->getUser();
        if (null !== $user) {
            $user = $this->userService->convertToUser($user);
            $modalWindows = $this->modalWindowRepository->findLastByAge($user->member?->age);
            $surveys = $this->surveyRepository->findActive($user->entity);
            $modalWindows = array_merge($modalWindows, $surveys);

            if (!empty($modalWindows)) {
                foreach($modalWindows as $modalWindow) {
                    $index = $user->licenceNumber.'-'.(new ReflectionClass($modalWindow))->getShortName().'-'.$modalWindow->getId();

                    if (!in_array($index, $modalWindowShowOn)) {
                        $modalWindowShowOn[] = $index;
                        $session->set('modal_window_show_on', json_encode($modalWindowShowOn));
                
                        return $modalWindow;
                    }
                }
            }
        }

        return null;
    }
}
