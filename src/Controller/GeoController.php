<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\GeoService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class GeoController extends AbstractController
{

    #[Route('geo/department', name: 'geo_department', methods: ['post', 'get'], options:['expose' => true])]
    public function getDepartment(
        Request $request,
        GeoService $geoService
    ): JsonResponse {
        $commune = $request->query->get('q');

        if (!$commune) {
            return new JsonResponse([]);
        }

        $data = $geoService->getCommunesByName($commune);

        $communes = [];
        if (!empty($data)) {
            foreach($data as $commune) {
                $communes[] = [
                    'id' => (int) $commune['code'],
                    'text' => $commune['nom'] . ' - ' . $commune['departement']['code'] .' '. $commune['departement']['nom'],
                ];
            }
        }

        return new JsonResponse($communes);
    }
}