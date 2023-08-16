<?php

declare(strict_types=1);

namespace App\UseCase\User;

use App\Dto\DtoTransformer\PaginatorDtoTransformer;
use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Form\Admin\UserFilterType;
use App\Repository\UserRepository;
use App\Service\PaginatorService;
use App\Service\SeasonService;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
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
    public string $exportFilename;

    public function __construct(
        private PaginatorService $paginator,
        public SeasonService $seasonService,
        private FormFactoryInterface $formFactory,
        private UrlGeneratorInterface $urlGenerator,
        private UserDtoTransformer $userDtoTransformer,
        public UserRepository $userRepository,
        private PaginatorDtoTransformer $paginatorDtoTransformer
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
            $filters = $form->getData();
            $filtered = true;
            $request->query->set('p', 1);
            $form = $this->createForm($filters);
        }

        $session->set($this->filterName, $filters);
        $query = $this->getQuery($filters);

        $this->setRedirect($request);

        $users = $this->paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);

        $paginator = $this->paginatorDtoTransformer->fromEntity($users, ['filtered' => (int) $filtered]);

        return [
            'users' => $this->userDtoTransformer->fromEntities($users),
            'paginator' => $paginator,
            'form' => $form->createView(),
            'count' => $paginator->total,
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
            $this->exportFilename
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    public function emailsToClipboard(Request $request): string
    {
        $session = $request->getSession();
        $filters = $session->get($this->filterName);
        $query = $this->getQuery($filters);
        $users = $query->getQuery()->getResult();
        $emails = [];
        if (!empty($users)) {
            foreach ($this->userDtoTransformer->fromEntities($users) as $user) {
                $emails[] = $user->mainEmail;
            }
        }

        return implode(',', $emails);
    }

    public function choices(array $filters, ?string $fullName): array
    {
        $filters['fullName'] = $fullName;
        $filters['user'] = null;
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

    private function createForm(array $filters): FormInterface
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
        'fullName' => null,
        'status' => 'SEASON_' . $this->seasonService->getCurrentSeason(),
        'levels' => null,
        ];
    }

    private function getExportContent(array $users): string
    {
        $content = [];
        $row = ['Numéro de licence', 'Nom', 'Prénom', 'Groupe ou Niveau', 'Mail contact principal', 'Date de naissance', 'Année', '3 séances d\'essai'];
        $content[] = implode(',', $row);

        foreach ($this->userDtoTransformer->fromEntities($users) as $user) {
            $isTesting = ($user->lastLicence?->isFinal) ? 0 : 1;
            $row = [$user->licenceNumber, $user->member->name, $user->member->firstName, $user->level?->title, $user->mainEmail, $user->member->birthDate, $user->lastLicence->season, $isTesting];
            $content[] = implode(',', $row);
        }

        return implode(PHP_EOL, $content);
    }
}
