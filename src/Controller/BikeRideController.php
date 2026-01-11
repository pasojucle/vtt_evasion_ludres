<?php

declare(strict_types=1);

namespace App\Controller;

use App\UseCase\BikeRide\GetSchedule;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class BikeRideController extends AbstractController
{
    public function __construct(
        private GetSchedule $getSchedule
    ) {
    }


    #[Route('/member/programme', name: 'member_schedule', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function memberList(): Response
    {
        return $this->redirectToRoute('schedule');
    }


    #[Route('/programme/{period}/{year}/{month}/{day}', name: 'schedule', methods: ['GET', 'POST'], defaults:['period' => null, 'year' => null, 'month' => null, 'day' => null])]
    public function list(
        Request $request,
        ?string $period,
        ?int $year,
        ?int $month,
        ?int $day
    ): Response {
        $response = $this->getSchedule->execute($request, $period, $year, $month, $day);
        if (array_key_exists('redirect', $response)) {
            return $this->redirectToRoute($response['redirect'], $response['filters']);
        }

        return $this->render('bike_ride/list.html.twig', $response['parameters']);
    }
}
