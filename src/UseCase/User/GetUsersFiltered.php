<?php

declare(strict_types=1);

namespace App\UseCase\User;

use App\Entity\User;
use ReflectionClass;
use App\Service\SeasonService;
use Doctrine\ORM\QueryBuilder;
use App\Service\PaginatorService;
use App\Form\Admin\UserFilterType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Dto\DtoTransformer\UserDtoTransformer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\HeaderUtils;
use App\Dto\DtoTransformer\PaginatorDtoTransformer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class GetUsersFiltered
{
    public int $statusType;
    public string $filterName;
    public string $statusPlaceholder;
    public string $remoteRoute;
    public string $exportFilename;
    public bool $statusIsRequire;

    public function __construct(
        private readonly PaginatorService $paginator,
        protected SeasonService $seasonService,
        private readonly FormFactoryInterface $formFactory,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly UserDtoTransformer $userDtoTransformer,
        protected UserRepository $userRepository,
        private readonly PaginatorDtoTransformer $paginatorDtoTransformer,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    abstract protected function getQuery(array $filters): QueryBuilder;

    abstract protected function getStatusChoices(): ?array;

    abstract protected function getPermissionChoices(): ?array;

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
        $session->set($this->filterName, $this->filtersToSession($filters));
        $query = $this->getQuery($filters);

        $this->setRedirect($request);

        $users = $this->paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);

        $paginator = $this->paginatorDtoTransformer->fromEntities($users, ['filtered' => (int) $filtered]);

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

        foreach ($users as $user) {
            $emails[] = $user->getMainIdentity()->getEmail();
        }


        return implode(',', $emails);
    }

    public function choices(array $filters): array
    {
        if (array_key_exists('user', $filters)) {
            unset($filters['user']);
        }
        
        $query = $this->getQuery($filters);
        $users = $query->getQuery()->getResult();

        $results = [];
        /** @var User $user */
        foreach ($users as $user) {
            $results[] = [
                'value' => $user->getId(),
                'text' => $user->getMainIdentity()->getFullName(),
            ];
        }

        return $results;
    }

    private function createForm(array $filters): FormInterface
    {
        return $this->formFactory->create(UserFilterType::class, $filters, [
            'status_choices' => $this->getStatusChoices(),
            'permission_choices' => $this->getPermissionChoices(),
            'status_is_require' => $this->statusIsRequire,
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
        return ($filtered && null !== $request->getSession()->get($this->filterName))
            ? $this->sessionToFilters($request->getSession()->get($this->filterName))
            : $this->getDefaultFilters();
    }

    private function filtersToSession(array $filters): array
    {
        $fitersToSession = [];
        foreach ($filters as $name => $value) {
            if (is_object($value)) {
                $reflexionClass = new ReflectionClass($value);
                $value = [
                    'id' => $value->getId(),
                    'entity' => $reflexionClass->getName(),
                ];
            }
            $fitersToSession[$name] = $value;
        }

        return $fitersToSession;
    }

    private function sessionToFilters(array $session): array
    {
        $sessionToFilters = [];
        foreach ($session as $name => $value) {
            if (is_array($value) && array_key_exists('entity', $value)) {
                $value = $this->entityManager->getRepository($value['entity'])->find($value['id']);
            }
            $sessionToFilters[$name] = $value;
        }

        return $sessionToFilters;
    }

    public function getDefaultFilters(): array
    {
        return [
            'user' => null,
            'query' => null,
            'status' => 'SEASON_' . $this->seasonService->getCurrentSeason(),
            'levels' => null,
            'permission' => null,
        ];
    }

    private function getExportContent(array $users): string
    {
        $content = [];
        $row = ['Numéro de licence', 'Nom', 'Prénom', 'Groupe ou Niveau', 'Mail contact principal', 'Date de naissance', 'Lieu de naissance', 'Département de naissance', 'Pays de naissance', 'Année', '3 séances d\'essai'];
        $content[] = implode(',', $row);

        foreach ($users as $user) {
            $userDto = $this->userDtoTransformer->fromEntity($user);
            $isTesting = ($userDto->lastLicence?->isYearly) ? 0 : 1;
            $row = [
                $userDto->licenceNumber,
                $userDto->member->name,
                $userDto->member->firstName,
                $userDto->level?->title,
                $userDto->mainEmail,
                $userDto->member->birthDate,
                $userDto->member->birthPlace,
                $userDto->member->birthDepartment,
                $userDto->member->birthCountry,
                $userDto->lastLicence->shortSeason,
                $isTesting,
                $userDto->lastLicence->state['label']
            ];
            $content[] = implode(',', $row);
        }

        return implode(PHP_EOL, $content);
    }
}
