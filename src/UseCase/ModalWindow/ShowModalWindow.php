<?php

declare(strict_types=1);

namespace App\UseCase\ModalWindow;

use App\Dto\DtoTransformer\ModalWindowDtoTransformer;
use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Dto\ModalWindowDto;
use App\Entity\Licence;
use App\Entity\User;
use App\Repository\ModalWindowRepository;
use App\Repository\OrderHeaderRepository;
use App\Repository\SurveyRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

class ShowModalWindow
{
    public function __construct(
        private RequestStack $requestStack,
        private Security $security,
        private ModalWindowRepository $modalWindowRepository,
        private SurveyRepository $surveyRepository,
        private OrderHeaderRepository $orderHeaderRepository,
        private ModalWindowDtoTransformer $modalWindowDtoTransformer,
        private UserDtoTransformer $userDtoTransformer
    ) {
    }

    public function execute(): ?ModalWindowDto
    {
        $session = $this->requestStack->getCurrentRequest()->getSession();
        $modalWindowShowOn = (null !== $session->get('modal_window_show_on'))
            ? json_decode($session->get('modal_window_show_on'), true)
            : [];
            
        /** @var User $user */
        $user = $this->security->getUser();

        $modalWindows = (null !== $user)
            ? $this->getUserModalWindows($user)
            : $this->modalWindowRepository->findPublic();

        if (!empty($modalWindows)) {
            $modalWidowSDto = $this->modalWindowDtoTransformer->fromEntities($modalWindows);

            foreach ($modalWidowSDto as $modalWindow) {
                if (!in_array($modalWindow->index, $modalWindowShowOn)) {
                    $modalWindowShowOn[] = $modalWindow->index;
                    $session->set('modal_window_show_on', json_encode($modalWindowShowOn));
                    return $modalWindow;
                }
            }
        }

        return null;
    }

    private function getUserModalWindows(User $user): array
    {
        $userDto = $this->userDtoTransformer->fromEntity($user);

        $modalWindows = $this->modalWindowRepository->findByAge($userDto->member?->age);
        $surveys = $this->surveyRepository->findActiveAndWithoutResponse($user);
        $modalWindows = array_merge($modalWindows, $surveys);
        $orderHeaderToValidate = $this->orderHeaderRepository->findOneOrderNotEmpty($user);

        if (null !== $orderHeaderToValidate) {
            $modalWindows = array_merge($modalWindows, [$orderHeaderToValidate]);
        }

        if (Licence::STATUS_IN_PROCESSING === $userDto->seasonLicence?->status) {
            $modalWindows = array_merge($modalWindows, [$userDto->seasonLicence]);
        }
        return $modalWindows;
    }
}
