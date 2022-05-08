<?php

declare(strict_types=1);

namespace App\UseCase\User;

use App\Entity\User;
use App\Form\Admin\ParticipationFilterType;
use App\Service\SeasonService;
use Doctrine\ORM\QueryBuilder;
use App\ViewModel\UserPresenter;
use App\ViewModel\UserViewModel;
use Symfony\Component\Form\Form;
use App\Service\PaginatorService;
use App\ViewModel\UsersPresenter;
use App\Form\Admin\UserFilterType;
use App\Repository\UserRepository;
use App\ViewModel\SessionPresenter;
use App\ViewModel\SessionsPresenter;
use App\Repository\SessionRepository;
use App\Service\IndemnityService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GetParticipation
{
    public string $filterName = 'user_participation';
    public string $remoteRoute;

    public function __construct(
        private PaginatorService $paginator,
        private FormFactoryInterface $formFactory,
        private UrlGeneratorInterface $urlGenerator,
        private UserPresenter $userPresenter,
        private SessionPresenter $sessionPresenter,
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

        $this->setRedirect($request, $user);

        $sessions = $this->paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);

        $this->userPresenter->present($user);
        $this->sessionsPresenter->present($sessions);
        return [
            'user' => $this->userPresenter->viewModel(),
            'sessions' =>  $this->sessionsPresenter->viewModel()->sessions,
            'total_indemnities' =>  $this->indemnityService->getUserIndemnities($user, ),
            'form' => $form->createView(),
            'lastPage' => $this->paginator->lastPage($sessions),
            'current_filters' => [
                'filtered' => (int) $filtered,
                'user' => $user->getId(),
            ],
            'count' => $this->paginator->total($sessions),
        ];
    }

    private function createForm(array $filters): Form
    {
        return $this->formFactory->create(ParticipationFilterType::class, $filters);
    }

    private function setRedirect(Request $request, User $user): void
    {
        $request->getSession()->set('admin_user_redirect', $this->urlGenerator->generate($request->get('_route'), [
            'user' => $user->getId(),
            'filtered' => true,
            'p' => $request->query->get('p'),
        ]));
    }

    private function getFilters(Request $request, bool $filtered): array
    {
        return ($filtered) ? $request->getSession()->get($this->filterName) : [
        'season' => 'SEASON_'.$this->seasonService->getCurrentSeason(),
        'bikeRideType' => null,
        ];
    }
}
