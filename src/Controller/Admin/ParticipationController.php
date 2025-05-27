<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Repository\SessionRepository;
use App\UseCase\User\GetParticipations;
use DateInterval;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/participation', name: 'admin_participation', methods: ['GET'])]
class ParticipationController extends AbstractController
{
    public function __construct(
        private readonly SessionRepository $sessionRepository,
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

    #[Route('/mensuelle/{isSchool}', name: '_monthly', methods: ['GET'], options: ['expose' => true])]
    #[IsGranted('PARTICIPATION_VIEW')]
    public function monthly(
        bool $isSchool
    ): JsonResponse {
        $today = new DateTimeImmutable();
        $filters = ['period' => ['startAt' => $today->sub(new DateInterval('P6M')), 'endAt' => $today], 'isSchool' => (bool) $isSchool];
        
        return new JsonResponse([
            'format' => 'card',
            'membersPrecences' => [
                ['data' => $this->sessionRepository->findMemberpresence($filters), 'color' => "rgba(230,132,27,1)"],
            ],
        ]);
    }
}
