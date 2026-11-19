<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\UseCase\User\GetOverviewSeason;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin', name: 'admin_')]
class MemberAnalyticsController extends AbstractController
{
    public function __construct(
        private readonly GetOverviewSeason $getOverviewSeason,
    ) {
    }

    #[Route('/synthese/saison', name: 'overview_season_list', methods: ['GET', 'POST'])]
    #[IsGranted('USER_SHARE')]
    public function overviewSeasonMembers(
        Request $request,
        bool $filtered,
        int $tab,
    ): Response {
        return $this->render('user/admin/overviewSeason.html.twig', $this->getOverviewSeason->getMembers($request, $filtered, $tab));
    }

    #[Route('/export/synthese/saison', name: 'overview_season_export', methods: ['GET', 'POST'])]
    #[IsGranted('USER_SHARE')]
    public function exportOverviewSeason(
        Request $request,
    ): Response {
        return $this->getOverviewSeason->export($request);
    }

    #[Route('/emails/synthese/saison', name: 'overview_season_email_to_clipboard', methods: ['GET'])]
    #[IsGranted('USER_SHARE')]
    public function overviewSeasonToClicpboard(
        Request $request
    ): JsonResponse {
        return new JsonResponse($this->getOverviewSeason->emailsToClipboard($request));
    }
}
