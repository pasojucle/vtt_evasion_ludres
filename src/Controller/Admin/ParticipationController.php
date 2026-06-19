<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\State\Participation\Provider\ParticipationMonthlyProvider;
use App\UseCase\User\GetParticipations;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/participation', name: 'admin_participation', methods: ['GET'])]
class ParticipationController extends AbstractController
{
    public function __construct(
        private readonly GetParticipations $getParticipations,
    ) {
    }

    #[Route('s/{filtered}', name: '_list', defaults: ['filtered' => false], methods: ['GET', 'POST'])]
    #[IsGranted('PARTICIPATION_VIEW')]
    public function participations(Request $request, bool $filtered): Response
    {
        return $this->render('participation/admin/list.html.twig', $this->getParticipations->execute($request, $filtered));
    }

    #[Route('/export', name: 's_export', methods: ['GET', 'POST'])]
    #[IsGranted('PARTICIPATION_VIEW')]
    public function export(Request $request): Response
    {
        return $this->getParticipations->export($request);
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
