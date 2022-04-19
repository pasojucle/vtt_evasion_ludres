<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\BikeRideType;
use App\Entity\Level;
use App\Form\Admin\BikeRideTypeType;
use App\Form\Admin\LevelType;
use App\Service\OrderByService;
use App\Service\PaginatorService;
use App\Repository\LevelRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\BikeRideTypeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
        Request $request
    ): Response {
        $query = $this->bikeRideTypeRepository->findBikeRideTypeQuery();
        $bikeRideTypes = $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);

        return $this->render('bike_ride_type/admin/list.html.twig', [
            'bikeRideTypes' => $bikeRideTypes,
            'lastPage' => $paginator->lastPage($bikeRideTypes),
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
            dump($bikeRideType);
            // $this->entityManager->persist($bikeRideType);
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_bike_ride_types');
        }

        return $this->render('bike_ride_type/admin/edit.html.twig', [
            'bike_ride_type' => $bikeRideType,
            'form' => $form->createView(),
        ]);
    }
}