<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\BikeRideDtoTransformer;
use App\Entity\BikeRide;
use App\Entity\Summary;
use App\Form\Admin\SummaryType;
use App\State\Summary\Processor\SummaryDeleteProcessor;
use App\State\Summary\Provider\SummaryDeleteProvider;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/actualite/', name: 'admin_summary_')]
class SummaryController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private BikeRideDtoTransformer $bikeRideDtoTransformer
    ) {
    }
    
    #[Route('list/{bikeRide}', name: 'list', methods: ['GET'])]
    #[IsGranted('SUMMARY_EDIT', 'bikeRide')]
    public function list(
        BikeRide $bikeRide
    ): Response {
        return $this->render('summary/admin/list.html.twig', [
            'summaries' => $bikeRide->getSummaries(),
            'bike_ride' => $this->bikeRideDtoTransformer->fromEntity($bikeRide),
        ]);
    }

    #[Route('add/{bikeRide}', name: 'add', methods: ['GET', 'POST'])]
    #[IsGranted('SUMMARY_ADD')]
    public function add(
        Request $request,
        BikeRide $bikeRide
    ): Response {
        $summary = new Summary();
        $summary->setCreatedAt(new DateTimeImmutable())
            ->setBikeRide($bikeRide);
        $form = $this->createForm(SummaryType::class, $summary);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $summary = $form->getData();
            $this->entityManager->persist($summary);
            $this->entityManager->flush();
            return $this->redirectToRoute('admin_summary_list', ['bikeRide' => $summary->getBikeRide()->getId()]);
        }

        return $this->render('summary/admin/edit.html.twig', [
            'summary' => $summary,
            'form' => $form->createView(),
        ]);
    }

    #[Route('edit/{summary}', name: 'edit', methods: ['GET', 'POST'])]
    #[IsGranted('SUMMARY_EDIT', 'summary')]
    public function edit(
        Request $request,
        Summary $summary
    ): Response {
        $form = $this->createForm(SummaryType::class, $summary);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            return $this->redirectToRoute('admin_summary_list', ['bikeRide' => $summary->getBikeRide()->getId()]);
        }

        return $this->render('summary/admin/edit.html.twig', [
            'summary' => $summary,
            'form' => $form->createView(),
        ]);
    }

    #[Route('delete/{summary}', name: 'delete', methods: ['GET', 'POST'])]
    #[IsGranted('SUMMARY_EDIT', 'summary')]
    public function delete(
        Request $request,
        SummaryDeleteProcessor $processor,
        SummaryDeleteProvider $provider,
        Summary $summary
    ): Response {
        $response = new Response("OK", Response::HTTP_OK);
        $form = $this->createForm(FormType::class, null, [
            'action' => $request->getUri(),
            'attr' => ['data-action' => 'turbo:submit-end->modal#handleFormSubmit']
        ]);
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted()) {
            if ($form->isValid()) {

                return $this->redirectToRoute('admin_summary_list', [
                    'bikeRide' => $processor->process($summary),
                ]);
            }
            $response = new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->render('component/_dialog.modal.html.twig', [
            'form' => $form->createView(),
            'dialog' => $provider->mapToView($summary),
        ], $response);
    }
}
