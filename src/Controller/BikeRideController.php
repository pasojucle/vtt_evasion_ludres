<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\DtoTransformer\BikeRideDtoTransformer;
use App\Entity\BikeRide;
use App\Form\GuestRegistryType;
use App\Form\SessionGuestAddType;
use App\Service\ProjectDirService;
use App\UseCase\Guest\SendLink;
use App\UseCase\BikeRide\GetTrackFile;
use App\UseCase\BikeRide\GetSchedule;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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
    #[IsGranted('USER_LIST')]
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


    #[Route('/randonnee/{bikeRide}/{slug}', name: 'bike_ride_detail', requirements:['bikeRide' => '\d+'], methods: ['GET', 'POST', 'POST'])]
    public function detail(
        Request $request,
        BikeRide $bikeRide,
        Request $request,
        string $slug,
        BikeRideDtoTransformer $bikeRideDtoTransformer,
        SendLink $sendLink,
    ): Response {
        $response = new Response("OK", Response::HTTP_OK);
        $errrorMessage = null;
        $form = $this->createForm(GuestRegistryType::class);
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted()) {
            if ($form->isValid()) {
                $email = trim($form->getData()->getEmail());
                $result = $sendLink->execute($email, $bikeRide);
                if ($result['success']) {
                    return $this->render('bike_ride/_frame_detail.html.twig', [
                        'email' => $email,
                    ]);
                }
                $errrorMessage = $result['message'];
            }
            $response = new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->render('bike_ride/detail.html.twig', [
            'errorMessage' => $errrorMessage,
            'form' => $form->createView(),
            'bikeRide' => $bikeRideDtoTransformer->fromEntity($bikeRide),
            'isRegistrationEnabled' => $bikeRide->registrationEnabled(),
        ]);
    }

    #[Route('/randonnee/traces/{bikeRide}/{slug}', name: 'bike_ride_tracks', methods: ['GET'])]
    public function getTracks(
        BikeRide $bikeRide,
        string $slug,
        BikeRideDtoTransformer $bikeRideDtoTransformer
    ): Response {
        return $this->render('bike_ride/tracks.html.twig', [
            'bikeRide' => $bikeRideDtoTransformer->fromEntity($bikeRide),
        ]);
    }
        
    #[Route('/bikeride/track/{filename}/{mimeType}', name: 'bike_ride_track', methods: ['GET'])]
    public function getTrack(
        string $filename,
        string $mimeType,
        GetTrackFile $getTrackFile
    ): Response {
        return $getTrackFile->execute($filename, $mimeType);
    }
}
