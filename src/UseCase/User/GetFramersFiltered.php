<?php

declare(strict_types=1);

namespace App\UseCase\User;

use App\Entity\BikeRide;
use App\Form\Admin\FramerFilterType;
use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use App\ViewModel\Session\SessionsPresenter;
use App\ViewModel\UsersPresenter;
use App\ViewModel\UserViewModel;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GetFramersFiltered
{
    private const FILTER_NAME = 'admin_framer_filters';

    public function __construct(
        private FormFactoryInterface $formFactory,
        private UrlGeneratorInterface $urlGenerator,
        private UsersPresenter $usersPresenter,
        private UserRepository $userRepository,
        private SessionRepository $sessionRepository,
        private SessionsPresenter $sessionsPresenter
    ) {
    }

    public function list(Request $request, BikeRide $bikeRide, bool $filtered): array
    {
        $session = $request->getSession();
        $filters = $this->getFilters($request, $bikeRide, $filtered);

        $form = $this->createForm($filters);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $filters = $form->getData();
            $filtered = true;
            $request->query->set('p', 1);
            $form = $this->createForm($filters);
        }

        $session->set(self::FILTER_NAME, $filters);

        $users = $this->userRepository->findFramers($filters)->getQuery()->getResult();

        $this->setRedirect($request, $bikeRide);

        $this->usersPresenter->present($users);

        return [
            'framers' => $this->addUserAvailability($this->usersPresenter->viewModel()->users, $filters),
            'form' => $form->createView(),
        ];
    }

    private function addUserAvailability(?array $users, array $filters): array
    {
        $sessions = $this->sessionRepository->findFramersByBikeRide($filters['bikeRideId']);
        $this->sessionsPresenter->present($sessions);

        $sessionsByUser = [];
        foreach ($this->sessionsPresenter->viewModel()->sessions as $session) {
            $sessionsByUser[$session->entity->getUser()->getId()] = $session;
        }


        $userWithAvailability = [];
        foreach ($users as $user) {
            $userId = ($user instanceof UserViewModel) ? $user->entity->getId() : $user->getId();

            $availability = (array_key_exists($userId, $sessionsByUser))
                ? $sessionsByUser[$userId]->availability
                : [
                    'class' => ['badge' => 'person person-rays', 'icon' => '<i class="fa-solid fa-person-rays"></i>'],
                    'text' => 'session.availability.undefined',
                    'value' => 0,
                ];

            if (null === $filters['availability'] || $filters['availability'] === $availability['value']) {
                $userWithAvailability[] = [
                    'user' => $user,
                    'availability' => $availability,

                ];
            }
        }

        return $userWithAvailability;
    }

    public function choices(array $filters, ?string $fullName): array
    {
        $filters['fullName'] = $fullName;
        $filters['user'] = null;

        $query = $this->userRepository->findFramers($filters);

        $users = $query->getQuery()->getResult();

        $framers = $this->addUserAvailability($users, $filters);

        $response = [];

        foreach ($framers as $framer) {
            $response[] = [
                'id' => $framer['user']->getId(),
                'text' => $framer['user']->GetFirstIdentity()->getName() . ' ' . $framer['user']->GetFirstIdentity()->getFirstName(),
            ];
        }

        return $response;
    }

    private function createForm(array $filters): FormInterface
    {
        return $this->formFactory->create(FramerFilterType::class, $filters, [
            'filters' => $filters,
        ]);
    }

    private function setRedirect(Request $request, BikeRide $bikeRide): void
    {
        $request->getSession()->set('admin_user_redirect', $this->urlGenerator->generate($request->get('_route'), [
            'bikeRide' => $bikeRide->getId(),
            'filtered' => true,
            'p' => $request->query->get('p'),
        ]));
    }

    private function getFilters(Request $request, BikeRide $bikeRide, bool $filtered): array
    {
        return ($filtered && null !== $request->getSession()->get(self::FILTER_NAME)) ? $request->getSession()->get(self::FILTER_NAME) : [
            'user' => null,
            'fullName' => null,
            'availability' => null,
            'bikeRideId' => $bikeRide->getId(),
        ];
    }
}
