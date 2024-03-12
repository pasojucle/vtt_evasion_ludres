<?php

declare(strict_types=1);

namespace App\UseCase\User;

use DateTime;
use App\Dto\UserDto;
use App\Entity\User;
use App\Dto\SessionDto;
use App\Entity\Session;
use App\Dto\BikeRideDto;
use App\Service\LevelService;
use App\Service\SeasonService;
use App\Repository\SessionRepository;
use Symfony\Component\Form\FormInterface;
use App\Repository\BikeRideTypeRepository;
use App\Form\Admin\ParticipationFilterType;
use Symfony\Component\HttpFoundation\Request;
use App\Dto\DtoTransformer\UserDtoTransformer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormFactoryInterface;
use App\Dto\DtoTransformer\SessionDtoTransformer;
use Symfony\Component\HttpFoundation\HeaderUtils;
use App\Dto\DtoTransformer\BikeRideDtoTransformer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GetParticipations
{
    public string $filterName = 'user_participation';
    public string $remoteRoute;

    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly UserDtoTransformer $userDtoTransformer,
        private readonly BikeRideDtoTransformer $bikeRideDtoTransformer,
        private readonly SessionDtoTransformer $sessionDtoTransformer,
        private readonly SessionRepository $sessionRepository,
        private readonly SeasonService $seasonService,
        private readonly BikeRideTypeRepository $bikeRideTypeRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly LevelService $levelService,
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
            'levels' => null,
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

    public function export(Request $request): Response
    {
        $session = $request->getSession();
        $filters = $session->get($this->filterName);
        $sessions = $this->sessionRepository->findByFilters($filters);
        $users = $this->getUsers($sessions);
        $content = [];
        $this->addExportHeader($content, $filters);
        $this->addExportContent($content, $users, $sessions);

        $response = new Response(implode(PHP_EOL, $content));
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            'export_participations.csv'
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    private function addExportHeader(array &$content, array $filters): void
    {
        if (isset($filters['startAt']) && isset($filters['endAt'])) {
            $content[] = sprintf('Du %s au %s', $filters['startAt']->format('d/m/Y'), $filters['endAt']->format('d/m/Y'));
        }

        if (isset($filters['bikeRideType'])) {
            $content[] = sprintf('Type de sortie : %s', $filters['bikeRideType']->getName());
        }

        if (isset($filters['levels'])) {
            $levelsToStr = $this->levelService->getLevelsAndTypesToStr();
            $levels = [];
            foreach($filters['levels'] as $level) {
                $levels[] = $levelsToStr[$level];
            }
            $content[] = sprintf('Niveau(x) : %s', implode(' - ', $levels));
        }
        $content[] = '';
    }

    private function addExportContent(array &$content, array $users, array $sessions,): void
    {
        $row = [''];
        /** @var UserDto $user */
        foreach($users as $user) {
            $row[] = $user->member->fullName;
        }
        $content[] = implode(',', $row);

        $participationsByBikeRide = $this->getParticipationsByBikeRide($users, $sessions);

        /** @var BikeRideDto $bikeRide */
        foreach($participationsByBikeRide as $bikeRide) {
            $row = [$bikeRide['entity']->period . ' - ' . $bikeRide['entity']->title];
            foreach ($bikeRide['sessions'] as $session) {
                $participation = 'Absent';
                if ($session instanceof SessionDto) {
                    $participation = $session->userIsOnSiteToStr;
                    if ($session->indemnityStr) {
                        $participation .= " - ". $session->indemnityStr;
                    }
                }
                $row[] = $participation;
            }
            $content[] = implode(',', $row);
        }
    }

    public function paginate(array $allUsers, array $sessions, Request $request, int $limit): array
    {
        $currentPage = $request->query->getInt('p') ?: 1;
        $lastPage = (int) ceil(count($allUsers) / $limit);

        $offset = $limit * ($currentPage - 1);
        $users = array_slice($allUsers, $offset, $limit);
        $unpresent = ['userIsOnSiteToHtml' => '<i class="fa-solid fa-user-xmark alert-danger"></i>'];
        
        return [
            'users' => $users,
            'bikeRides' => $this->getParticipationsByBikeRide($users, $sessions, $unpresent),
            'previous' => (1 < $currentPage) ? $currentPage - 1 : null,
            'next' => ($currentPage < $lastPage) ? $currentPage + 1 : null
        ];
    }

    private function getParticipationsByBikeRide(array $users, array $sessions, ?array $unpresent = null): array
    {
        $participationsByBikeRide = [];
        foreach ($users as $user) {
            foreach ($sessions as $session) {
                $bikeRide = $session->getCluster()->getBikeRide();
                if (!array_key_exists($bikeRide->getId(), $participationsByBikeRide)) {
                    $participationsByBikeRide[$bikeRide->getId()] = [
                        'entity' => $this->bikeRideDtoTransformer->getHeaderFromEntity($bikeRide),
                        'sessions' => [],
                    ];
                }
                if ($user->id === $session->getUser()->getId()) {
                    $participationsByBikeRide[$bikeRide->getId()]['sessions'][$user->id] = $this->sessionDtoTransformer->getPresence($session);
                }
                if (!array_key_exists($user->id, $participationsByBikeRide[$bikeRide->getId()]['sessions'])) {
                    $participationsByBikeRide[$bikeRide->getId()]['sessions'][$user->id] = $unpresent;
                }
            }
        }

        return $participationsByBikeRide;
    }
}
