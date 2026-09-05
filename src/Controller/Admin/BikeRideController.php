<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\BikeRideDtoTransformer;
use App\Dto\DtoTransformer\ClusterDtoTransformer;
use App\Dto\Filter\ActivityFilter;
use App\Entity\BikeRide;
use App\Form\Admin\BikeRideType;
use App\Repository\BikeRideRepository;
use App\State\Activity\Processor\ActivityDeleteProcessor;
use App\State\Activity\Processor\ActivityRestoreProcessor;
use App\State\Activity\Processor\ActivityUpdateProcessor;
use App\State\Activity\Provider\ActivityAdminListProvider;
use App\State\Activity\Provider\ActivityDeleteProvider;
use App\State\Activity\Provider\ActivityUpdateProvider;
use App\UseCase\BikeRide\ExportBikeRide;
use App\UseCase\BikeRide\GetBikeRideFile;
use App\UseCase\BikeRide\GetEmailMembers;
use App\UseCase\User\GetFramersFiltered;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
class BikeRideController extends AbstractCrudController
{
    public function __construct(
        private BikeRideRepository $bikeRideRepository,
        private BikeRideDtoTransformer $bikeRideDtoTransformer,
        private ClusterDtoTransformer $clusterDtoTransformer,
    ) {
    }

    #[Route('/calendrier', name: 'admin_bike_ride_list', methods: ['GET'])]
    #[IsGranted('BIKE_RIDE_LIST')]
    public function adminList(
        Request $request,
        ActivityAdminListProvider $provider,
    ): Response {
        return $this->handleListPaginedAction(
            ActivityFilter::class,
            $provider,
            $request
        );
    }

    #[Route('/sortie', name: 'admin_bike_ride_add', methods: ['GET', 'POST'])]
    #[IsGranted('BIKE_RIDE_ADD')]
    public function adminAdd(
        Request $request,
        ActivityUpdateProvider $provider,
        ActivityUpdateProcessor $processor,
    ): Response {
        return $this->handleFormComponentAction(
            $request,
            new BikeRide(),
            $provider,
            $processor,
            BikeRideType::class,
        );
    }

    #[Route('/sortie/{bikeRide}', name: 'admin_bike_ride_edit', methods: ['GET', 'POST'], requirements:['bikeRide' => '\d+'])]
    #[IsGranted('BIKE_RIDE_EDIT', 'bikeRide')]
    public function adminEdit(
        Request $request,
        ActivityUpdateProvider $provider,
        ActivityUpdateProcessor $processor,
        ?BikeRide $bikeRide
    ): Response {
        return $this->handleFormComponentAction(
            $request,
            $bikeRide,
            $provider,
            $processor,
            BikeRideType::class,
        );
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
        if ($params['redirect']) {
            return $this->redirectToRoute('admin_bike_ride_framer_list', [
                'bikeRide' => $bikeRide->getId(),
                'filtered' => true,
            ]);
        }
        $params['bike_ride'] = $this->bikeRideDtoTransformer->fromEntity($bikeRide);

        return $this->render('bike_ride/admin/framer_list.html.twig', $params);
    }

    #[Route('/supprimer/sortie/{bikeRide}', name: 'admin_bike_ride_delete', methods: ['GET', 'POST'])]
    #[IsGranted('BIKE_RIDE_EDIT', 'bikeRide')]
    public function adminDelete(
        Request $request,
        ActivityDeleteProvider $provider,
        ActivityDeleteProcessor $processor,
        BikeRide $bikeRide
    ): Response {
        return $this->handleFormComponentAction(
            $request,
            $bikeRide,
            $provider,
            $processor,
        );
    }

    #[Route('/restaure/sortie/{bikeRide}', name: 'admin_bike_ride_restore', methods: ['GET'])]
    #[IsGranted('BIKE_RIDE_EDIT', 'bikeRide')]
    public function adminRestaure(
        Request $request,
        ActivityRestoreProcessor $processor,
        BikeRide $bikeRide
    ): Response {
        return $this->handleProcessAction(
            $request,
            $bikeRide,
            $processor,
        );
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

    #[Route('/bikeride/show/{filename}/{mimeType}', name: 'admin_bike_ride_show_file', methods: ['GET'])]
    public function showFile(
        string $filename,
        string $mimeType,
    ): Response {
        return $this->render('bike_ride/admin/preview.modal.html.twig', [
            'title' => base64_decode($filename),
            'file' => $this->generateUrl('admin_bike_ride_file', ['filename' => $filename, 'mimeType' => $mimeType]),
            'mime_type' => $mimeType,
        ]);
    }

    #[Route('/bikeride/track/{filename}/{mimeType}', name: 'admin_bike_ride_file', methods: ['GET'])]
    public function getFile(
        string $filename,
        string $mimeType,
        GetBikeRideFile $getBikeRideFile
    ): Response {
        return $getBikeRideFile->execute($filename, $mimeType);
    }
}
