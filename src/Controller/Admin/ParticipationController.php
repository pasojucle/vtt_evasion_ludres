<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Form\Admin\ParticipationFilterType;
use App\Repository\SessionRepository;

use App\Service\PaginatorService;
use App\UseCase\User\GetParticipations;
use DateInterval;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/participation', name: 'admin_participation', methods: ['GET'])]
class ParticipationController extends AbstractController
{
    public function __construct(
        private readonly SessionRepository $sessionRepository,
    ) {
    }
    #[Route('s/{filtered}', name: '_list', defaults: ['filtered' => false], methods: ['GET', 'POST'])]
    public function participations(Request $request, GetParticipations $getParticipations, bool $filtered): Response
    {
        return $this->render('participation/admin/list.html.twig', $getParticipations->execute($request, $filtered));
    }

    #[Route('/mensuelle/{isSchool}', name: '_monthly', methods: ['GET'], options: ['expose' => true])]
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
