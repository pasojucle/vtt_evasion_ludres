<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\BikeRideType;
use App\Entity\Indemnity;
use App\Entity\Level;
use App\Form\Admin\IndemnityType;
use App\UseCase\Indemnity\GetIndemnities;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/param/indemnite', name: 'admin_indemnity')]
class IndemnityController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Route('s}', name: '_list', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminList(
        GetIndemnities $getIndemnities
    ): Response {
        return $this->render('indemnity/admin/list.html.twig', [
            'indemnities' => $getIndemnities->execute(),
        ]);
    }

    #[Route('/edit/{indemnity}', name: '_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminEdit(
        Request $request,
        Indemnity $indemnity
    ): Response {
        $form = $this->createForm(IndemnityType::class, $indemnity, [
            'action' => $this->generateUrl('admin_indemnity_edit', [
                'indemnity' => $indemnity->getId(),
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
            'level' => $indemnity->getLevel(),
            'bikeRideType' => $indemnity->getBikeRideType(),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/add/{level}/{bikeRideType}', name: '_add', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminAdd(
        Request $request,
        Level $level,
        BikeRideType $bikeRideType
    ): Response {
        $indemnity = new Indemnity();
        $indemnity->setLevel($level)
            ->setBikeRideType($bikeRideType);
        $form = $this->createForm(IndemnityType::class, $indemnity, [
            'action' => $this->generateUrl('admin_indemnity_add', [
                'level' => $level->getId(),
                'bikeRideType' => $bikeRideType->getId(),
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
            'level' => $level,
            'bikeRideType' => $bikeRideType,
            'form' => $form->createView(),
        ]);
    }
}
