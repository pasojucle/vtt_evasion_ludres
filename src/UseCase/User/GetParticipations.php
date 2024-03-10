<?php

declare(strict_types=1);

namespace App\UseCase\User;

use App\Dto\DtoTransformer\BikeRideDtoTransformer;
use App\Dto\DtoTransformer\SessionDtoTransformer;
use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Dto\SessionDto;
use App\Entity\Session;
use App\Entity\User;
use App\Form\Admin\ParticipationFilterType;
use App\Repository\BikeRideTypeRepository;
use App\Repository\SessionRepository;
use App\Service\SeasonService;
use DateTime;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GetParticipations
{
    public string $filterName = 'user_participation';
    public string $remoteRoute;

    public function __construct(
        private FormFactoryInterface $formFactory,
        private UserDtoTransformer $userDtoTransformer,
        private BikeRideDtoTransformer $bikeRideDtoTransformer,
        private SessionDtoTransformer $sessionDtoTransformer,
        private SessionRepository $sessionRepository,
        private SeasonService $seasonService,
        private BikeRideTypeRepository $bikeRideTypeRepository,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function execute(Request $request, bool $filtered): array
    {
        $session = $request->getSession();
        $filters = $this->getFilters($request, $filtered);

        $form = $this->createForm($filters);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $filters = $form->getData();
            $filtered = true;
            $request->query->set('p', 1);
        }

        $session->set($this->filterName, $filters);
        $sessions = $this->sessionRepository->findByFilters($filters);
        $users = $this->getUsers($sessions);
        $paginator = $this->paginate($users, $sessions, $request, 4);

        return [
            'users' => $paginator['users'],
            'bike_rides' => $paginator['bikeRides'],
            'pannel' => [
                'previous' => $paginator['previous'],
                'next' => $paginator['next']
            ],
            'form' => $form->createView(),
            'referer' => $session->get('admin_user_redirect'),
        ];
    }

    private function createForm(array $filters): FormInterface
    {
        return $this->formFactory->create(ParticipationFilterType::class, $filters, [
            'action' => $this->urlGenerator->generate('admin_participation_list')
        ]);
    }

    private function getFilters(Request $request, bool $filtered): array
    {
        if ($filtered) {
            $filters = $request->getSession()->get($this->filterName);
            if ($filters['bikeRideType']) {
                $filters['bikeRideType'] = $this->bikeRideTypeRepository->find($filters['bikeRideType']->getId());
            }
            return $filters;
        }
        $period = $this->seasonService->getCurrentSeasonInterval();
        return  [
            'startAt' => $period['startAt'],
            'endAt' => $period['endAt'],
            'bikeRideType' => null,
        ];
    }

    private function getUsers(array $sessions): array
    {
        $userEntities = [];
        /** @var Session $session */
        foreach ($sessions as $session) {
            $user = $session->getUser();
            if (!array_key_exists($user->getId(), $userEntities)) {
                $userEntities[$user->getId()] = $user;
            }
        }
        $users = $this->userDtoTransformer->fromEntities($userEntities);

        $this->sortUsers($users);

        return $users;
    }

    private function getParticipations(array $sessions): array
    {
        $participations = ['bikeRides' => [], 'users' => []];
        /** @var Session $session */
        foreach ($sessions as $session) {
            $bikeRide = $session->getCluster()->getBikeRide();
            if (!array_key_exists($bikeRide->getId(), $participations['bikeRides'])) {
                $participations['bikeRides'][$bikeRide->getId()] = [
                    'bikeRide' => $bikeRide,
                    'sessions' => [],
                ];
            }
            $user = $session->getUser();
            if (!array_key_exists($user->getId(), $participations['users'])) {
                $participations['users'][$user->getId()] = $user;
            }
            $participations['bikeRides'][$bikeRide->getId()]['sessions'][$session->getUser()->getId()] = $session;
        }

        return $this->sortParticipations($participations);
    }

    private function sortParticipations(array $particiaptions): array
    {
        $users = $this->userDtoTransformer->fromEntities($particiaptions['users']);
        $bikeRides = $particiaptions['bikeRides'];
        $this->sortUsers($users);
        $this->sortBikeRides($bikeRides);
        $particiaptions['users'] = $users;
        $particiaptions['bikeRides'] = $bikeRides;
        return $particiaptions;
    }

    private function sortUsers(array &$users): void
    {
        uasort($users, function ($a, $b) {
            return strtolower($a->member->fullName) < strtolower($b->member->fullName) ? -1 : 1;
        });
    }

    private function sortBikeRides(array &$bikeRides): void
    {
        usort($bikeRides, function ($a, $b) {
            return $a['bikeRide']->startAt < $b['bikeRide']->startAt ? -1 : 1;
        });
    }

    public function export(Request $request, User $user)
    {
        $session = $request->getSession();
        $filters = $session->get($this->filterName);
        $query = $this->sessionRepository->findByUserAndFilters($user, $filters);
        $sessions = $query->getQuery()->getResult();
        $content = [];
        $this->addExportHeader($content, $user, $filters);
        $this->addExportContent($content, $sessions);

        $response = new Response(implode(PHP_EOL, $content));
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            sprintf('export_participation_%s.csv', $user->getLicenceNumber())
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    private function addExportHeader(array &$content, User $user, array $filters): void
    {
        $userDto = $this->userDtoTransformer->fromEntity($user);
        $row = [$userDto->member->fullName, $userDto->licenceNumber];
        $content[] = implode(',', $row);

        if (isset($filters['startAt']) && isset($filters['endAt'])) {
            $content[] = sprintf('Du %s au %s', $filters['startAt']->format('d/m/Y'), $filters['endAt']->format('d/m/Y'));
        }

        if (isset($filters['bikeRideType'])) {
            $content[] = sprintf('Type de sortie : %s', $filters['bikeRideType']->getName());
        }
        $content[] = '';
    }

    private function addExportContent(array &$content, array $sessions): void
    {
        $row = ['Date', 'Sortie', 'Présence'];
        $content[] = implode(',', $row);

        /** @var SessionDto $session */
        foreach ($this->sessionDtoTransformer->fromEntities($sessions) as $session) {
            $row = [$session->bikeRide->period, $session->bikeRide->title, $session->userIsOnSiteToStr];
            $content[] = implode(',', $row);
        }
    }

    public function paginate(array $allUsers, array $sessions, Request $request, int $limit): array
    {
        $currentPage = $request->query->getInt('p') ?: 1;
        $lastPage = (int) ceil(count($allUsers) / $limit);

        $offset = $limit * ($currentPage - 1);
        $users = array_slice($allUsers, $offset, $limit);
        $bikeRides = [];
        foreach ($users as $user) {
            foreach ($sessions as $session) {
                $bikeRide = $session->getCluster()->getBikeRide();
                if (!array_key_exists($bikeRide->getId(), $bikeRides)) {
                    $bikeRides[$bikeRide->getId()] = [
                        'entity' => $this->bikeRideDtoTransformer->getHeaderFromEntity($bikeRide),
                        'sessions' => [],
                    ];
                }
                if ($user->id === $session->getUser()->getId()) {
                    $bikeRides[$bikeRide->getId()]['sessions'][$user->id] = $this->sessionDtoTransformer->getPresence($session);
                }
                if (!array_key_exists($user->id, $bikeRides[$bikeRide->getId()]['sessions'])) {
                    $bikeRides[$bikeRide->getId()]['sessions'][$user->id] = ['userIsOnSiteToHtml' => '<i class="fa-solid fa-user-xmark alert-danger"></i>'];
                }
            }
        }
       
        return [
            'users' => $users,
            'bikeRides' => $bikeRides,
            'previous' => (1 < $currentPage) ? $currentPage - 1 : null,
            'next' => ($currentPage < $lastPage) ? $currentPage + 1 : null
        ];
    }
}
