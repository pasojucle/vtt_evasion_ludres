<?php

declare(strict_types=1);

namespace App\UseCase\User;

use App\Dto\DtoTransformer\PaginatorDtoTransformer;
use App\Dto\DtoTransformer\SessionDtoTransformer;
use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\User;
use App\Form\Admin\ParticipationFilterType;
use App\Repository\BikeRideTypeRepository;
use App\Repository\SessionRepository;
use App\Service\IndemnityService;
use App\Service\PaginatorService;
use App\Service\SeasonService;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class GetParticipation
{
    public string $filterName = 'user_participation';
    public string $remoteRoute;

    public function __construct(
        private PaginatorService $paginator,
        private PaginatorDtoTransformer $paginatorDtoTransformer,
        private FormFactoryInterface $formFactory,
        private UserDtoTransformer $userDtoTransformer,
        private SessionDtoTransformer $sessionDtoTransformer,
        private SessionRepository $sessionRepository,
        private SeasonService $seasonService,
        private IndemnityService $indemnityService,
        private BikeRideTypeRepository $bikeRideTypeRepository
    ) {
    }

    public function execute(Request $request, User $user, bool $filtered): array
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
        $query = $this->sessionRepository->findByUserAndFilters($user, $filters);

        $sessions = $this->paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);

        $season = ($filters['season']) ? (int) str_replace('SEASON_', '', $filters['season']) : null;

        return [
            'user' => $this->userDtoTransformer->fromEntity($user),
            'sessions' => $this->sessionDtoTransformer->fromEntities($sessions),
            'total_indemnities' => $this->indemnityService->getUserIndemnities($user, $season),
            'form' => $form->createView(),
            'paginator' => $this->paginatorDtoTransformer->fromEntities($sessions, ['filtered' => (int) $filtered, 'user' => $user->getId()]),
            'referer' => $session->get('admin_user_redirect'),
        ];
    }

    private function createForm(array $filters): FormInterface
    {
        return $this->formFactory->create(ParticipationFilterType::class, $filters);
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
        return  [
            'season' => 'SEASON_' . $this->seasonService->getCurrentSeason(),
            'bikeRideType' => null,
        ];
    }
}
