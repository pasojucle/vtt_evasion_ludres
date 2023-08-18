<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\User;
use App\Repository\ContentRepository;
use App\UseCase\BikeRide\GetSchedule;
use App\UseCase\User\GetBikeRides;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BikeRideController extends AbstractController
{
    public function __construct(
        private GetSchedule $getSchedule
    ) {
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

    #[Route('/mon-programme', name: 'user_bike_rides', methods: ['GET'])]
    public function userBikeRides(
        UserDtoTransformer $userDtoTransformer,
        GetBikeRides $getBikeRides,
        ContentRepository $contentRepository
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');
        /** @var ?User $user */
        $user = $this->getUser();

        return $this->render('bike_ride/user_list.html.twig', [
            'user' => $userDtoTransformer->fromEntity($user),
            'bikeRides' => $getBikeRides->execute($user),
            'backgrounds' => $contentRepository->findOneByRoute('user_account')?->getBackgrounds(),
        ]);
    }
}
