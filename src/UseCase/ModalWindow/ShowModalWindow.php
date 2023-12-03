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
use Exception;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

class ShowModalWindow
{
    public function __construct(
        private RequestStack $requestStack,
        private Security $security,
        private ModalWindowRepository $modalWindowRepository,
        private SurveyRepository $surveyRepository,
        private OrderHeaderRepository $orderHeaderRepository,
        private ModalWindowDtoTransformer $modalWindowDtoTransformer,
        private UserDtoTransformer $userDtoTransformer,
        private RouterInterface $router,
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

        if (!$userDto->lastLicence->isActive) {
            return [];
        }

        $modalWindows = $this->modalWindowRepository->findByAge($userDto->member?->age);
        $surveys = $this->surveyRepository->findActiveAndWithoutResponse($user);
        $modalWindows = array_merge($modalWindows, $surveys);
        $orderHeaderToValidate = $this->orderHeaderRepository->findOneOrderNotEmpty($user);

        if (null !== $orderHeaderToValidate) {
            $modalWindows = array_merge($modalWindows, [$orderHeaderToValidate]);
        }

        $search = [
            $this->requestStack->getCurrentRequest()->getScheme(),
            '://',
            $this->requestStack->getCurrentRequest()->headers->get('host'),
        ];
        $referer = str_replace($search, '', $this->requestStack->getCurrentRequest()->headers->get('referer'));
        try {
            $route = $this->router->match($referer);
        } catch (Exception) {
            $route = null;
        }

        if (Licence::STATUS_IN_PROCESSING === $userDto->lastLicence?->status && !str_contains($route['_route'], 'registration_form')) {
            $modalWindows = array_merge($modalWindows, [$user->getLastLicence()]);
        }
        return $modalWindows;
    }
}
