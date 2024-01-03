<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Repository\SessionRepository;
use DateInterval;

use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/participation', name: 'members_presence', methods: ['GET'])]
class MembersPresenceController extends AbstractController
{
    public function __construct(
        private readonly SessionRepository $sessionRepository,
    ) {
    }
    #[Route('/recherche', name: '_search', methods: ['GET'])]
    public function Search(): Response
    {
        return $this->render('members_presence/search.html.twig');
    }

    #[Route('/mensuelle', name: '_monthly', methods: ['GET'], options: ['expose' => true])]
    public function monthly(): JsonResponse
    {
        $today = new DateTimeImmutable();
        $filters = ['period' => ['startAt' => $today->sub(new DateInterval('P6M')), 'endAt' => $today]];
        
        return new JsonResponse(['format' => 'card', 'membersPrecences' => [$this->sessionRepository->findMemberpresence($filters)]]);
    }
}
