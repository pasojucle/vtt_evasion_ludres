<?php

declare(strict_types=1);

namespace App\UseCase\User;

use App\Entity\User;
use App\Form\Admin\ParticipationFilterType;
use App\Repository\SessionRepository;
use App\Service\IndemnityService;
use App\Service\PaginatorService;
use App\Service\SeasonService;
use App\ViewModel\SessionsPresenter;
use App\ViewModel\UserPresenter;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class GetParticipation
{
    public string $filterName = 'user_participation';
    public string $remoteRoute;

    public function __construct(
        private PaginatorService $paginator,
        private FormFactoryInterface $formFactory,
        private UserPresenter $userPresenter,
        private SessionsPresenter $sessionsPresenter,
        private SessionRepository $sessionRepository,
        private SeasonService $seasonService,
        private IndemnityService $indemnityService
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

        $this->userPresenter->present($user);
        $this->sessionsPresenter->present($sessions);

        return [
            'user' => $this->userPresenter->viewModel(),
            'sessions' => $this->sessionsPresenter->viewModel()->sessions,
            'total_indemnities' => $this->indemnityService->getUserIndemnities($user, (int) str_replace('SEASON_', '', $filters['season'])),
            'form' => $form->createView(),
            'lastPage' => $this->paginator->lastPage($sessions),
            'current_filters' => [
                'filtered' => (int) $filtered,
                'user' => $user->getId(),
            ],
            'count' => $this->paginator->total($sessions),
            'referer' => $session->get('admin_user_redirect'),
        ];
    }

    private function createForm(array $filters): FormInterface
    {
        return $this->formFactory->create(ParticipationFilterType::class, $filters);
    }

    private function getFilters(Request $request, bool $filtered): array
    {
        return ($filtered) ? $request->getSession()->get($this->filterName) : [
        'season' => 'SEASON_' . $this->seasonService->getCurrentSeason(),
        'bikeRideType' => null,
        ];
    }
}
