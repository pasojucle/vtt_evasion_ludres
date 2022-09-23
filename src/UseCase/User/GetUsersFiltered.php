<?php

declare(strict_types=1);

namespace App\UseCase\User;

use App\Form\Admin\UserFilterType;
use App\Repository\UserRepository;
use App\Service\PaginatorService;
use App\Service\SeasonService;
use App\ViewModel\UsersPresenter;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class GetUsersFiltered
{
    public int $statusType;
    public string $filterName;
    public string $statusPlaceholder;
    public string $remoteRoute;

    public function __construct(
        private PaginatorService $paginator,
        public SeasonService $seasonService,
        private FormFactoryInterface $formFactory,
        private UrlGeneratorInterface $urlGenerator,
        private UsersPresenter $usersPresenter,
        public UserRepository $userRepository
    ) {
    }

    abstract protected function getQuery(array $filters): QueryBuilder;

    abstract protected function getStatusChoices(): ?array;

    public function list(Request $request, bool $filtered): array
    {
        $session = $request->getSession();
        $filters = $this->getFilters($request, $filtered);

        $form = $this->createForm($filters);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $filters = $form->getData();dump($filters);
            $filtered = true;
            $request->query->set('p', 1);
            $form = $this->createForm($filters);
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
        $content = $this->getExportContent($users);

        $response = new Response($content);
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            'export_email.csv'
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    public function emailsToClipboard(Request $request): string
    {
        $session = $request->getSession();
        $filters = $session->get($this->filterName);
        $query = $this->getQuery($filters);
        $emails = $query->select('i.email')->getQuery()->getScalarResult();

        return implode(',', array_column($emails, 'email'));
    }

    public function choices(array $filters, ?string $fullName): array
    {
        $filters['fullName'] = $fullName;
        $query = $this->getQuery($filters);

        $users = $query->getQuery()->getResult();

        $response = [];

        foreach ($users as $user) {
            $response[] = [
                'id' => $user->getId(),
                'text' => $user->GetFirstIdentity()->getName() . ' ' . $user->GetFirstIdentity()->getFirstName(),
            ];
        }

        return $response;
    }

    private function createForm(array $filters): Form
    {
        return $this->formFactory->create(UserFilterType::class, $filters, [
            'status_choices' => $this->getStatusChoices(),
            'status_placeholder' => $this->statusPlaceholder,
            'filters' => $filters,
            'remote_route' => $this->remoteRoute,
        ]);
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
        return ($filtered && null !== $request->getSession()->get($this->filterName)) ? $request->getSession()->get($this->filterName) : [
        'user' => null,
        'status' => 'SEASON_' . $this->seasonService->getCurrentSeason(),
        'levels' => null,
        ];
    }

    private function getExportContent(array $users): string
    {
        $content = [];
        $row = ['Prénom', 'Nom', 'Mail', 'Date de naissance', 'Numéro de licence', 'Année', '3 séances d\'essai'];
        $content[] = implode(',', $row);

        if (!empty($users)) {
            foreach ($users as $user) {
                $identity = $user->getFirstIdentity();
                $licence = $user->getLastLicence();
                $row = [$identity->getFirstName(), $identity->getName(), $identity->getEmail(), $identity->getBirthDate()->format('d/m/Y'), $user->getLicenceNumber(), $licence->getSeason(), false === $licence->isFinal()];
                $content[] = implode(',', $row);
            }
        }

        return implode(PHP_EOL, $content);
    }
}
