<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\PaginatorDtoTransformer;
use App\Entity\BikeRideType;
use App\Entity\Enum\RegistrationEnum;
use App\Form\Admin\BikeRideTypeType;
use App\Repository\BikeRideTypeRepository;
use App\Repository\MessageRepository;
use App\Service\PaginatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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
        MessageRepository $messageRepository,
        Request $request
    ): Response {
        $query = $this->bikeRideTypeRepository->findBikeRideTypeQuery();
        $bikeRideTypes = $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);

        return $this->render('bike_ride_type/admin/list.html.twig', [
            'bikeRideTypes' => $bikeRideTypes,
            'paginator' => $paginatorDtoTransformer->fromEntities($bikeRideTypes),
            'settings' => [
                'actions' => [
                    ['name' => 'admin_message_add', 'params' => ['sectionName' => 'BIKE_RIDE_TYPE'], 'label' => '<i class="fa-solid fa-square-plus"></i> Ajouter un message'],
                ],
                'messages' => $messageRepository->findBySectionNameAndQuery('BIKE_RIDE_TYPE'),
            ],
        ]);
    }

    #[Route('/type-rando', name: 'bike_ride_type_add', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminAdd(
        Request $request,
    ): Response {
        $bikeRideType = new BikeRideType();
        $form = $this->createForm(BikeRideTypeType::class, $bikeRideType);
        $form->handleRequest($request);
        
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $bikeRideType = $form->getData();
            if (RegistrationEnum::CLUSTERS !== $bikeRideType->getRegistration()) {
                $bikeRideType->setClusters([]);
            }

            $this->entityManager->persist($bikeRideType);
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_bike_ride_types');
        }

        $showErrors = false;
        foreach ($form->getErrors() as $error) {
            if ('6e5212ed-a197-4339-99aa-5654798a4855' !== $error->getCause()->getCode()) {
                $showErrors = true;
            }
        }

        return $this->render('bike_ride_type/admin/edit.html.twig', [
            'bike_ride_type' => $bikeRideType,
            'form' => $form->createView(),
            'show_errors' => $showErrors,
        ]);
    }

    #[Route('/type-rando/{bikeRideType}', name: 'bike_ride_type_edit', methods: ['GET', 'POST'], requirements: ['bikeRideType' => '\d+'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminEdit(
        Request $request,
        BikeRideType $bikeRideType
    ): Response {
        $form = $this->createForm(BikeRideTypeType::class, $bikeRideType);
        $form->handleRequest($request);
        
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $bikeRideType = $form->getData();
            if (RegistrationEnum::CLUSTERS !== $bikeRideType->getRegistration()) {
                $bikeRideType->setClusters([]);
            }

            $this->entityManager->flush();

            return $this->redirectToRoute('admin_bike_ride_types');
        }

        return $this->render('bike_ride_type/admin/edit.html.twig', [
            'bike_ride_type' => $bikeRideType,
            'form' => $form->createView(),
        ]);
    }
}
