<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\BikeRide;
use App\Form\Admin\BikeRideType;
use App\Repository\BikeRideRepository;
use App\Service\BikeRideService;
use App\UseCase\BikeRide\EditBikeRide;
use App\UseCase\BikeRide\ExportBikeRide;
use App\ViewModel\BikeRidePresenter;
use App\ViewModel\UserPresenter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class BikeRideController extends AbstractController
{
    public function __construct(
        private BikeRideRepository $bikeRideRepository,
        private EntityManagerInterface $entityManager,
        private BikeRideService $bikeRideService,
        private UserPresenter $userPresenter
    ) {
    }

    #[Route('/', name: 'admin_home', methods: ['GET'])]
    public function adminHome()
    {
        return $this->redirectToRoute('admin_bike_rides');
    }

    #[Route('/calendrier/{period}/{year}/{month}/{day}', name: 'admin_bike_rides', methods: ['GET', 'POST'], defaults:['period' => null, 'year' => null, 'month' => null, 'day' => null])]
    public function adminList(
        Request $request,
        ?string $period,
        ?int $year,
        ?int $month,
        ?int $day
    ): Response {
        $response = $this->bikeRideService->getSchedule($request, $period, $year, $month, $day);

        if (array_key_exists('redirect', $response)) {
            return $this->redirectToRoute($response['redirect'], $response['filters']);
        }

        return $this->render('bike_ride/admin/list.html.twig', $response['parameters']);
    }

    #[Route('/sortie/{bikeRide}', name: 'admin_bike_ride_edit', methods: ['GET', 'POST'], defaults:['bikeRide' => null])]
    public function adminEdit(
        Request $request,
        EditBikeRide $editBikeRide,
        BikeRidePresenter $bikeRidePresenter,
        ?BikeRide $bikeRide
    ): Response {
        if (null === $bikeRide) {
            $bikeRide = new BikeRide();
        }
        $bikeRide = $this->bikeRideService->setDefaultContent($request, $bikeRide);
        $filters = $request->getSession()->get('admin_bike_rides_filters');
        $form = $this->createForm(BikeRideType::class, $bikeRide);

        if (!$request->isXmlHttpRequest()) {
            $form->handleRequest($request);
        }
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $editBikeRide->execute($form, $request);
            
            $this->addFlash('success', 'La sortie à bien été enregistrée');

            $filters = $this->bikeRideService->getFilters(BikeRide::PERIOD_MONTH, $bikeRide->getStartAt());

            return $this->redirectToRoute('admin_bike_rides', $filters);
        }

        $bikeRidePresenter->present($bikeRide);

        return $this->render('bike_ride/admin/edit.html.twig', [
            'form' => $form->createView(),
            'bikeRide' => $bikeRidePresenter->viewModel(),
            'bike_rides_filters' => ($filters) ? $filters : [],
        ]);
    }

    #[Route('/sortie/groupe/{bikeRide}', name: 'admin_bike_ride_cluster_show', methods: ['GET'])]
    public function adminClusterShow(
        Request $request,
        BikeRidePresenter $presenter,
        BikeRide $bikeRide
    ): Response {
        $filters = $request->getSession()->get('admin_bike_rides_filters');
        $request->getSession()->set('user_return', $this->generateUrl('admin_bike_ride_cluster_show', [
            'bikeRide' => $bikeRide->getId(),
        ]));
        $presenter->present($bikeRide);

        return $this->render('cluster/show.html.twig', [
            'bikeRide' => $presenter->viewModel(),
            'bike_rides_filters' => ($filters) ? $filters : [],
        ]);
    }

    #[Route('/sortie/export/{bikeRide}', name: 'admin_bike_ride_export', methods: ['GET', 'POST'], defaults:[])]
    public function adminBikeRideExport(
        ExportBikeRide $exportBikeRide,
        BikeRide $bikeRide
    ): Response {
        return $exportBikeRide->execute($bikeRide);
    }
}
