<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Core\Handler\ListExportHandler;
use App\Core\Handler\ListPaginatedHandler;
use App\Dto\Filter\ParticipationFilter;
use App\Entity\BikeRide;
use App\State\Participation\Provider\ParticipationListExportProvider;
use App\State\Participation\Provider\ParticipationListProvider;
use App\State\Participation\Provider\ParticipationMonthlyProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/participation', name: 'admin_participation', methods: ['GET'])]
class ParticipationController extends AbstractController
{
    #[Route('s/{filtered}', name: '_list', defaults: ['filtered' => false], methods: ['GET', 'POST'])]
    #[IsGranted('PARTICIPATION_VIEW')]
    public function participations(
        Request $request,
        ParticipationListProvider $provider,
        ListPaginatedHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $provider
        );
    }

    #[Route('/export', name: '_export', methods: ['GET', 'POST'])]
    #[IsGranted('PARTICIPATION_VIEW')]
    public function export(
        Request $request,
        ParticipationListExportProvider $provider,
        ListExportHandler $handler,
    ): Response
    {
        return $handler->handle(
            $request,
            ParticipationFilter::class,
            $provider,
        );
    }

    #[Route('/show/{activity}', name: '_show', methods: ['GET', 'POST'])]
    #[IsGranted('PARTICIPATION_VIEW')]
    public function show(
        Request $request,
        BikeRide $activity,
    ): Response
    {
        return new Response();
    }

    #[Route('/mensuelle/{isSchool}', name: '_monthly', methods: ['GET'])]
    #[IsGranted('PARTICIPATION_VIEW')]
    public function monthly(
        ParticipationMonthlyProvider $provider,
        bool $isSchool
    ): JsonResponse {
        return new JsonResponse($provider->mapToView($isSchool));
    }
}
