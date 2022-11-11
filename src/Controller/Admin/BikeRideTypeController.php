<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\BikeRideType;
use App\Form\Admin\BikeRideTypeType;
use App\Repository\BikeRideTypeRepository;
use App\Service\PaginatorService;
use App\ViewModel\Paginator\PaginatorPresenter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BikeRideTypeController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private BikeRideTypeRepository $bikeRideTypeRepository
    ) {
    }

    #[Route('/admin/types-rando', name: 'admin_bike_ride_types', methods: ['GET'])]
    public function adminList(
        PaginatorService $paginator,
        PaginatorPresenter $paginatorPresenter,
        Request $request
    ): Response {
        $query = $this->bikeRideTypeRepository->findBikeRideTypeQuery();
        $bikeRideTypes = $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);
        $paginatorPresenter->present($bikeRideTypes);

        return $this->render('bike_ride_type/admin/list.html.twig', [
            'bikeRideTypes' => $bikeRideTypes,
            'paginator' => $paginatorPresenter->viewModel(),
        ]);
    }

    #[Route('/admin/type-rando/{bikeRideType}', name: 'admin_bike_ride_type_edit', methods: ['GET', 'POST'], defaults:['bikeRideType' => null])]
    public function adminEdit(
        Request $request,
        ?BikeRideType $bikeRideType
    ): Response {
        $form = $this->createForm(BikeRideTypeType::class, $bikeRideType);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $bikeRideType = $form->getData();

            $this->entityManager->flush();

            return $this->redirectToRoute('admin_bike_ride_types');
        }

        return $this->render('bike_ride_type/admin/edit.html.twig', [
            'bike_ride_type' => $bikeRideType,
            'form' => $form->createView(),
        ]);
    }
}
