<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\BikeRideDtoTransformer;
use App\Dto\DtoTransformer\ClusterDtoTransformer;
use App\Entity\BikeRide;
use App\Form\Admin\BikeRideType;
use App\Repository\BikeRideRepository;
use App\UseCase\BikeRide\EditBikeRide;
use App\UseCase\BikeRide\ExportBikeRide;
use App\UseCase\BikeRide\GetEmailMembers;
use App\UseCase\BikeRide\GetFilters;
use App\UseCase\BikeRide\GetSchedule;
use App\UseCase\User\GetFramersFiltered;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
class BikeRideController extends AbstractController
{
    public function __construct(
        private BikeRideRepository $bikeRideRepository,
        private EntityManagerInterface $entityManager,
        private GetSchedule $getSchedule,
        private GetFilters $getFilters,
        private BikeRideDtoTransformer $bikeRideDtoTransformer,
        private ClusterDtoTransformer $clusterDtoTransformer,
    ) {
    }

    #[Route('/calendrier/{period}/{year}/{month}/{day}', name: 'admin_bike_rides', methods: ['GET', 'POST'], defaults:['period' => null, 'year' => null, 'month' => null, 'day' => null])]
    #[IsGranted('BIKE_RIDE_LIST')]
    public function adminList(
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

        return $this->render('bike_ride/admin/list.html.twig', $response['parameters']);
    }

    #[Route('/sortie', name: 'admin_bike_ride_add', methods: ['GET', 'POST'])]
    #[IsGranted('BIKE_RIDE_ADD')]
    public function adminAdd(
        Request $request,
        EditBikeRide $editBikeRide
    ): Response {
        $bikeRide = null;
        $filters = $request->getSession()->get('admin_bike_rides_filters');
        $form = $this->createForm(BikeRideType::class, $bikeRide);

        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $bikeRide = $editBikeRide->execute($form, $request, true);

            $this->addFlash('success', 'La sortie à bien été enregistrée');

            $filters = $this->getFilters->execute(BikeRide::PERIOD_MONTH, $bikeRide->getStartAt());

            return $this->redirectToRoute('admin_bike_rides', $filters);
        }

        return $this->render('bike_ride/admin/edit.html.twig', [
            'form' => $form->createView(),
            'bikeRide' => $this->bikeRideDtoTransformer->fromEntity($bikeRide),
            'bike_rides_filters' => ($filters) ? $filters : [],
        ]);
    }


    #[Route('/sortie/{bikeRide}', name: 'admin_bike_ride_edit', methods: ['GET', 'POST'], requirements:['bikeRide' => '\d+'])]
    #[IsGranted('BIKE_RIDE_EDIT', 'bikeRide')]
    public function adminEdit(
        Request $request,
        EditBikeRide $editBikeRide,
        ?BikeRide $bikeRide
    ): Response {
        $filters = $request->getSession()->get('admin_bike_rides_filters');
        $form = $this->createForm(BikeRideType::class, $bikeRide);

        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $bikeRide = $editBikeRide->execute($form, $request);

            $this->addFlash('success', 'La sortie à bien été enregistrée');

            $filters = $this->getFilters->execute(BikeRide::PERIOD_MONTH, $bikeRide->getStartAt());

            return $this->redirectToRoute('admin_bike_rides', $filters);
        }

        return $this->render('bike_ride/admin/edit.html.twig', [
            'form' => $form->createView(),
            'bikeRide' => $this->bikeRideDtoTransformer->fromEntity($bikeRide),
            'bike_rides_filters' => ($filters) ? $filters : [],
        ]);
    }

    #[Route('/sortie/groupe/{bikeRide}', name: 'admin_bike_ride_cluster_show', methods: ['GET'])]
    #[IsGranted('BIKE_RIDE_VIEW', 'bikeRide')]
    public function adminClusterShow(
        Request $request,
        BikeRide $bikeRide,
    ): Response {
        $filters = $request->getSession()->get('admin_bike_rides_filters');
        $request->getSession()->set('admin_user_redirect', $this->generateUrl('admin_bike_ride_cluster_show', [
            'bikeRide' => $bikeRide->getId(),
        ]));

        return $this->render('bike_ride/admin/show.html.twig', [
            'bikeRide' => $this->bikeRideDtoTransformer->fromEntity($bikeRide),
            'clusters' => $this->clusterDtoTransformer->headerFromBikeRide($bikeRide),
            'bike_rides_filters' => ($filters) ? $filters : [],
        ]);
    }

    #[Route('/sortie/export/{bikeRide}', name: 'admin_bike_ride_export', methods: ['GET', 'POST'], defaults:[])]
    #[IsGranted('BIKE_RIDE_EDIT', 'bikeRide')]
    public function adminBikeRideExport(
        ExportBikeRide $exportBikeRide,
        BikeRide $bikeRide
    ): Response {
        return $exportBikeRide->execute($bikeRide);
    }


    #[Route('/sortie_choices', name: 'admin_bike_ride_choices', methods: ['GET'])]
    public function bikeRideChoices(
        Request $request
    ): JsonResponse {
        $query = $request->query->get('q');
        $bikeRides = (null !== $query)
            ? $this->bikeRideRepository->findLike($query)
            : $this->bikeRideRepository->findAllDESC();

        $response = [];
        foreach ($this->bikeRideDtoTransformer->fromEntities($bikeRides) as $bikeRide) {
            $response[] = [
                'id' => $bikeRide->id,
                'text' => $bikeRide->period . ' - ' . $bikeRide->title,
            ];
        }

        return new JsonResponse($response);
    }

    #[Route('/sortie/encadrement/{bikeRide}/{filtered}', name: 'admin_bike_ride_framer_list', methods: ['GET', 'POST'], defaults:['filtered' => false])]
    #[IsGranted('BIKE_RIDE_VIEW', 'bikeRide')]
    public function adminBikeRideFramerList(
        GetFramersFiltered $getFramersFiltered,
        Request $request,
        BikeRide $bikeRide,
        bool $filtered
    ) {
        $params = $getFramersFiltered->list($request, $bikeRide, $filtered);
        $params['bike_ride'] = $this->bikeRideDtoTransformer->fromEntity($bikeRide);
        return $this->render('bike_ride/admin/framer_list.html.twig', $params);
    }

    #[Route('/supprimer/sortie/{bikeRide}', name: 'admin_bike_ride_delete', methods: ['GET', 'POST'])]
    #[IsGranted('BIKE_RIDE_EDIT', 'bikeRide')]
    public function adminLevelDelete(
        Request $request,
        BikeRide $bikeRide
    ): Response {
        $form = $this->createForm(FormType::class, null, [
            'action' => $this->generateUrl(
                'admin_level_delete',
                [
                    'level' => $bikeRide->getId(),
                ]
            ),
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $bikeRide->setDeleted(true);
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_bike_rides');
        }

        return $this->render('bike_ride/admin/delete.modal.html.twig', [
            'bike_ride' => $this->bikeRideDtoTransformer->fromEntity($bikeRide),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/emails/adherents/{bikeRide}', name: 'admin_bike_ride_members_email_to_clipboard', methods: ['GET'])]
    #[IsGranted('BIKE_RIDE_VIEW', 'bikeRide')]
    public function adminEmailMembers(
        GetEmailMembers $getEmailMembers,
        BikeRide $bikeRide
    ): JsonResponse {
        return new JsonResponse($getEmailMembers->execute($bikeRide));
    }

    #[Route('/autocomplete', name: 'admin_bike_ride_autocomplete', methods: ['GET'])]
    #[IsGranted('BIKE_RIDE_LIST')]
    public function autocomplete(
        Request $request,
        BikeRideRepository $bikeRideRepository,
    ): JsonResponse {
        $query = $request->query->get('query');
        $results = [];
        $bikeRides = ($query) ? $bikeRideRepository->findLike($query) : $bikeRideRepository->findAllDESC();
        foreach ($bikeRides as $bikeRide) {
            $results[] = [
                'value' => $bikeRide->getId(),
                'text' => $bikeRide->__toString(),
            ];
        }

        return new JsonResponse(['results' => $results]);
    }
}
