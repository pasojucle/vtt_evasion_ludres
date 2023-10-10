<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\BikeRideType;
use App\Service\PaginatorService;
use App\Form\Admin\BikeRideTypeType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\BikeRideTypeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Dto\DtoTransformer\PaginatorDtoTransformer;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/param', name: 'admin_', methods: ['GET'])]
class BikeRideTypeController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private BikeRideTypeRepository $bikeRideTypeRepository
    ) {
    }

    #[Route('/types-rando', name: 'bike_ride_types', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminList(
        PaginatorService $paginator,
        PaginatorDtoTransformer $paginatorDtoTransformer,
        Request $request
    ): Response {
        $query = $this->bikeRideTypeRepository->findBikeRideTypeQuery();
        $bikeRideTypes = $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);

        return $this->render('bike_ride_type/admin/list.html.twig', [
            'bikeRideTypes' => $bikeRideTypes,
            'paginator' => $paginatorDtoTransformer->fromEntities($bikeRideTypes),
        ]);
    }

    #[Route('/type-rando/{bikeRideType}', name: 'bike_ride_type_edit', methods: ['GET', 'POST'], defaults:['bikeRideType' => null])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminEdit(
        Request $request,
        ?BikeRideType $bikeRideType
    ): Response {
        $form = $this->createForm(BikeRideTypeType::class, $bikeRideType);
        $form->handleRequest($request);
        
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $bikeRideType = $form->getData();
            if (BikeRideType::REGISTRATION_CLUSTERS !== $bikeRideType->getRegistration()) {
                $bikeRideType->setClusters([]);
            }

            $this->entityManager->persist($bikeRideType);
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_bike_ride_types');
        }

        return $this->render('bike_ride_type/admin/edit.html.twig', [
            'bike_ride_type' => $bikeRideType,
            'form' => $form->createView(),
        ]);
    }
}
