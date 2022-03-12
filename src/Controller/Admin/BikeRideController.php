<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\BikeRide;
use App\Entity\Session;
use App\Form\Admin\BikeRideType;
use App\Repository\BikeRideRepository;
use App\Repository\SessionRepository;
use App\Service\BikeRideService;
use App\Service\FilenameService;
use App\Service\UserService;
use App\ViewModel\UserPresenter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class BikeRideController extends AbstractController
{
    public function __construct(
        private BikeRideRepository $bikeRideRepository,
        private RequestStack $requestStack,
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
        ?BikeRide $bikeRide
    ): Response {
        if (null === $bikeRide) {
            $bikeRide = new BikeRide();
        }
        $bikeRide = $this->bikeRideService->setDefaultContent($request, $bikeRide);
        $filters = $this->requestStack->getSession()->get('admin_bike_rides_filters');
        $form = $this->createForm(BikeRideType::class, $bikeRide);

        if (!$request->isXmlHttpRequest()) {
            $form->handleRequest($request);
        }
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $bikeRide = $form->getData();

            $clusters = $bikeRide->getClusters();

            if ($clusters->isEmpty($bikeRide)) {
                $this->bikeRideService->createClusters($bikeRide);
            }

            $this->entityManager->persist($bikeRide);
            $this->entityManager->flush();
            $this->addFlash('success', 'La sortie à bien été enregistrée');

            $filters = $this->bikeRideService->getFilters(BikeRide::PERIOD_MONTH, $bikeRide->getStartAt());

            return $this->redirectToRoute('admin_bike_rides', $filters);
        }

        return $this->render('bike_ride/admin/edit.html.twig', [
            'form' => $form->createView(),
            'bikeRide' => $bikeRide,
            'bike_rides_filters' => ($filters) ? $filters : [],
        ]);
    }

    #[Route('/sortie/groupe/{bikeRide}', name: 'admin_bike_ride_cluster_show', methods: ['GET'])]
    public function adminClusterShow(
        BikeRideService $bikeRideService,
        BikeRide $bikeRide
    ): Response {
        $filters = $this->requestStack->getSession()->get('admin_bike_rides_filters');
        $this->requestStack->getSession()->set('user_return', $this->generateUrl('admin_bike_ride_cluster_show', [
            'bikeRide' => $bikeRide->getId(),
        ]));

        return $this->render('cluster/show.html.twig', [
            'bikeRide' => $bikeRideService->getBikeRideWithPresentsByCluster($bikeRide),
            'bike_rides_filters' => ($filters) ? $filters : [],
        ]);
    }

    #[Route('/sortie/export/{bikeRide}', name: 'admin_bike_ride_export', methods: ['GET', 'POST'], defaults:[])]
    public function adminBikeRideExport(
        SessionRepository $sessionRepository,
        UserService $userService,
        FilenameService $filenameService,
        BikeRide $bikeRide
    ): Response {
        $sessions = $sessionRepository->findByBikeRide($bikeRide);
        $separator = ',';
        $fileContent = [];
        $fileContent[] = $bikeRide->getTitle().' - '.$bikeRide->getStartAt()->format('d/m/Y');
        $fileContent[] = '';
        $row = ['n° de Licence', 'Nom', 'Prénom', 'Présent', 'Niveau'];
        $fileContent[] = implode($separator, $row);
        if (!empty($sessions)) {
            foreach ($sessions as $session) {
                if (Session::AVAILABILITY_UNAVAILABLE !== $session->getAvailability()) {
                    $this->userPresenter->present($session->getUser());

                    $member = $this->userPresenter->viewModel()->getMember();
                    $present = ($session->isPresent()) ? 'oui' : 'non';
                    $row = [$this->userPresenter->viewModel()->getLicenceNumber(), $member['name'], $member['firstName'], $present, $this->userPresenter->viewModel()->getLevel()];
                    $fileContent[] = implode($separator, $row);
                }
            }
        }
        $filename = $bikeRide->getTitle().'_'.$bikeRide->getStartAt()->format('Y_m_d');
        $filename = $filenameService->clean($filename).'.csv';
        $response = new Response(implode(PHP_EOL, $fileContent));
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $filename,
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
