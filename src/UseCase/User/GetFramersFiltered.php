<?php

declare(strict_types=1);

namespace App\UseCase\User;

use App\Dto\DtoTransformer\SessionDtoTransformer;
use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Dto\UserDto;
use App\Entity\BikeRide;
use App\Form\Admin\FramerFilterType;
use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class GetFramersFiltered
{
    private const FILTER_NAME = 'admin_framer_filters';

    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly UserDtoTransformer $userDtoTransformer,
        private readonly UserRepository $userRepository,
        private readonly SessionRepository $sessionRepository,
        private readonly SessionDtoTransformer $sessionDtoTransformer,
        private readonly TranslatorInterface $translator,
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

        return [
            'framers' => $this->addUserAvailability($this->userDtoTransformer->fromEntities($users), $filters),
            'form' => $form->createView(),
        ];
    }

    private function addUserAvailability(?array $users, array $filters): array
    {
        $sessions = $this->sessionRepository->findFramersByBikeRide($filters['bikeRideId']);

        $sessionsByUser = [];
        foreach ($this->sessionDtoTransformer->fromEntities($sessions) as $session) {
            $sessionsByUser[$session->user->id] = $session;
        }


        $userWithAvailability = [];
        foreach ($users as $user) {
            $userId = ($user instanceof UserDto) ? $user->id : $user->getId();

            $availability = (array_key_exists($userId, $sessionsByUser))
                ? $sessionsByUser[$userId]->availability
                : [
                    'class' => ['badge' => 'person person-rays', 'icon' => '<i class="fa-solid fa-person-rays"></i>'],
                    'text' => $this->translator->trans('session.availability.undefined'),
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

    public function choices(array $filters): array
    {
        $filters['user'] = null;

        $query = $this->userRepository->findFramers($filters);

        $users = $query->getQuery()->getResult();

        $framers = $this->addUserAvailability($users, $filters);

        $results = [];

        foreach ($framers as $framer) {
            $results[] = [
                'value' => $framer['user']->getId(),
                'text' => $framer['user']->GetFirstIdentity()->getName() . ' ' . $framer['user']->GetFirstIdentity()->getFirstName(),
            ];
        }

        return $results;
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
