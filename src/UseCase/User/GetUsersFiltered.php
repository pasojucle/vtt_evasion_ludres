<?php

declare(strict_types=1);

namespace App\UseCase\User;

use App\Form\UserFilterType;
use App\Repository\UserRepository;
use Doctrine\ORM\QueryBuilder;
use App\Service\LicenceService;
use App\Service\PaginatorService;
use App\ViewModel\UsersPresenter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class GetUsersFiltered
{
    public int $statusType;
    public string $filterName;

    public function __construct(
        private PaginatorService $paginator,
        private LicenceService $licenceService,
        private FormFactoryInterface $formFactory,
        private UrlGeneratorInterface $urlGenerator,
        private UsersPresenter $usersPresenter,
        public UserRepository $userRepository
    ) {
    }

    abstract protected function getQuery(array $filters): QueryBuilder;

    public function list(Request $request, bool $filtered): array
    {
        $session = $request->getSession();
        $filters = $this->getFilters($request, $filtered);

        $form = $this->formFactory->create(UserFilterType::class, $filters, [
            'statusType' => $this->statusType,
        ]);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $filters = $form->getData();
            $filtered = true;
            $request->query->set('p', 1);
        }

        $session->set($this->filterName, $filters);
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

    public function export(Request $request): Response
    {
        $session = $request->getSession();
        $filters = $session->get($this->filterName);

        $query = $this->getQuery($filters);
        $users = $query->getQuery()->getResult();
        $content = $this->getContent($users);

        $response = new Response($content);
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            'export_email.csv'
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    private function setRedirect(Request $request): void
    {
        $request->getSession()->set('admin_user_redirect', $this->urlGenerator->generate($request->get('_route'), [
            'filtered' => true,
            'p' => $request->query->get('p'),
        ]));
    }

    private function getFilters(Request $request, bool $filtered): array
    {
        return ($filtered) ? $request->getSession()->get($this->filterName) : [
        'fullName' => null,
        'status' => 'SEASON_'.$this->licenceService->getCurrentSeason(),
        'level' => null,
        ];
    }

    private function getContent(array $users): string
    {
        $content = [];
        $row = ['Prénom', 'Nom', 'Mail', 'Date de naissance', 'Numéro de licence', 'Année', '3 séances d\'essai'];
        $content[] = implode(',', $row);

        if (!empty($users)) {
            foreach ($users as $user) {
                $identity = $user->getFirstIdentity();
                $licence = $user->getLastLicence();
                $row = [$identity->getFirstName(), $identity->getName(), $identity->getEmail(), $identity->getBirthDate()->format('d/m/Y'), $user->getLicenceNumber(), $licence->getSeason(), !$licence->isFinal()];
                $content[] = implode(',', $row);
            }
        }

        return implode(PHP_EOL, $content);
    }
}
