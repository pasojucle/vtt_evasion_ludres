<?php

declare(strict_types=1);

namespace App\UseCase\User;

use App\Dto\DtoTransformer\PaginatorDtoTransformer;
use App\Dto\DtoTransformer\SessionDtoTransformer;
use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Dto\SessionDto;
use App\Entity\Member;
use App\Form\Admin\ParticipationFilterType;
use App\Repository\BikeRideTypeRepository;
use App\Repository\SessionRepository;
use App\Service\IndemnityService;
use App\Service\PaginatorService;
use App\Service\SeasonService;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
        private BikeRideTypeRepository $bikeRideTypeRepository,
    ) {
    }

    public function execute(Request $request, Member $member, bool $filtered): array
    {
        $session = $request->getSession();
        $filters = $this->getFilters($request, $filtered);

        $form = $this->createForm($filters, $member);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $filters = $form->getData();
            $filtered = true;
            $request->query->set('p', 1);
        }

        $session->set($this->filterName, $filters);
        $query = $this->sessionRepository->findByUserAndFilters($member, $filters);

        $sessions = $this->paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);

        return [
            'user' => $this->userDtoTransformer->fromEntity($member),
            'sessions' => $this->sessionDtoTransformer->fromEntities($sessions),
            'total_indemnities' => $this->indemnityService->getUserIndemnities($member, $filters),
            'form' => $form->createView(),
            'paginator' => $this->paginatorDtoTransformer->fromEntities($sessions, ['filtered' => (int) $filtered, 'user' => $member->getId()]),
            'referer' => $session->get('admin_user_redirect'),
        ];
    }

    private function createForm(array $filters, ?Member $member): FormInterface
    {
        return $this->formFactory->create(ParticipationFilterType::class, $filters, [
            'user' => $member,
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
        $period = $this->seasonService->getCurrentSeasonPeriod();
        return  [
            'startAt' => $period['startAt'],
            'endAt' => $period['endAt'],
            'bikeRideType' => null,
        ];
    }

    public function export(Request $request, Member $member): Response
    {
        $session = $request->getSession();
        $filters = $session->get($this->filterName);
        $query = $this->sessionRepository->findByUserAndFilters($member, $filters);
        $sessions = $query->getQuery()->getResult();
        $content = [];
        $this->addExportHeader($content, $member, $filters);
        $this->addExportContent($content, $sessions);

        $response = new Response(implode(PHP_EOL, $content));
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            sprintf('export_participation_%s.csv', $member->getLicenceNumber())
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    private function addExportHeader(array &$content, Member $member, array $filters): void
    {
        $userDto = $this->userDtoTransformer->fromEntity($member);
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
}
