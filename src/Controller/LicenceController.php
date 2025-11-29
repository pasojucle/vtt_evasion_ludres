<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Licence;
use App\Repository\LicenceRepository;
use App\Service\SeasonService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/licence', name: 'licence')]
class LicenceController extends AbstractController
{
    #[Route('/autocomplete', name: '_autocomplete', methods: ['GET'])]
    public function autocomplete(
        Request $request,
        LicenceRepository $licenceRepository,
        SeasonService $seasonService,
    ): JsonResponse {
        $currentSeason = $seasonService->getCurrentSeason();
        $query = $request->query->get('query');
        $results = [];
        $licence = ($query)
            ? $licenceRepository->findOneLicenceByNumerAndsSeason($query, $currentSeason)
            : null;
        if ($licence) {
            $results[] = [
                'value' => $licence->getId(),
                'text' => $licence->getUser()->getLicenceNumber(),
            ];
        }

        return new JsonResponse(['results' => $results]);
    }
}
