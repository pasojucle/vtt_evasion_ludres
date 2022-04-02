<?php

declare(strict_types=1);

namespace App\UseCase\User;

use App\Form\UserFilterType;
use App\Repository\UserRepository;
use App\Service\LicenceService;
use App\Service\PaginatorService;
use App\ViewModel\UsersPresenter;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class GetUsersFiltered
{
    public const STATUS_TYPE = '';

    public function __construct(
        private PaginatorService $paginator,
        private LicenceService $licenceService,
        private FormFactoryInterface $formFactory,
        private UrlGeneratorInterface $urlGenerator,
        protected UserRepository $userRepository,
        private UsersPresenter $usersPresenter
    ) {
    }

    public function execute(Request $request, bool $filtered): array
    {
        $session = $request->getSession();
        $filters = $this->getFilters($request, $filtered);

        $form = $this->formFactory->create(UserFilterType::class, $filters, [
            'statusType' => self::STATUS_TYPE,
        ]);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $filters = $form->getData();
            $filtered = true;
            $request->query->set('p', 1);
        }

        $session->set($this->sessionVarNameFilter($request), $filters);
        $query = $this->getQuery($filters);

        $this->setRedirect($request);

        $users = $this->paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);

        $this->usersPresenter->present($users);

        return [
            'users' => $this->usersPresenter->viewModel()->users,
            'lastPage' => $this->paginator->lastPage($users),
            'form' => $form->createView(),
            'current_filters' => [
                'filtered' => (int) $filtered,
            ],
            'count' => $this->paginator->total($users),
        ];
    }

    private function sessionVarNameFilter(Request $request): string
    {
        return $request->get('_route').'_filters';
    }

    private function setRedirect(Request $request): void
    {
        $request->getSession()->set($request->get('_route').'_redirect', $this->urlGenerator->generate($request->get('_route'), [
            'filtered' => true,
            'p' => $request->query->get('p'),
        ]));
    }

    private function getFilters(Request $request, bool $filtered): array
    {
        return ($filtered) ? $request->getSession()->get($this->sessionVarNameFilter($request)) : [
        'fullName' => null,
        'status' => 'SEASON_'.$this->licenceService->getCurrentSeason(),
        'level' => null,
    ];
    }

    abstract protected function getQuery(array $filters): QueryBuilder;
}
