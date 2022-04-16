<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\BikeRide;
use App\Entity\Indemnity;
use App\Form\Admin\IndemnityType;
use App\Repository\IndemnityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\ViewModel\Indemnity\IndemnitiesPresenter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/indemnite', name: 'admin_indemnity')]
class IndemnityController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
        
    }
    #[Route('s}', name: '_list', methods: ['GET'])]
    public function adminList(
        IndemnityRepository $indemnityRepository,
        IndemnitiesPresenter $indemnitiesPresenter
    ): Response {

        $indemnitiesPresenter->present($indemnityRepository->findAll());
        return $this->render('indemnity/admin/list.html.twig', [
            'bike_ride_types' => [BikeRide::TYPE_SCHOOL, BikeRide::TYPE_CRITERIUM],
            'indemnities' => $indemnitiesPresenter->viewModel()->indemnities,
        ]);
    }

    #[Route('/edit/{indemnity}', name: '_edit', methods: ['GET', 'POST'], defaults: ['indemnity' => null])]
    public function adminEdit(
        Request $request,
        ?Indemnity $indemnity
    ): Response {
        $form = $this->createForm(IndemnityType::class, $indemnity, [
            'action' => $this->generateUrl ('admin_indemnity_edit', [
                    'indemnity' => $indemnity?->getId(),
            ]),
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('post') && $form->isSubmitted() && $form->isValid()) {
            $amount = $form->getData();
            $this->entityManager->persist($amount);
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_indemnity_list');
        }

        return $this->render('indemnity/admin/edit.modal.html.twig', [
            'indemnity' => $indemnity,
            'form' => $form->createView(),
        ]);
    }
}