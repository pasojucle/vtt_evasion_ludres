<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\DiseaseKind;
use App\Form\Admin\DiseaseKindType;
use App\Repository\DiseaseKindRepository;
use App\Service\OrderByService;
use App\Service\PaginatorService;
use App\ViewModel\Paginator\PaginatorPresenter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DiseaseKindController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DiseaseKindRepository $diseaseKindRepository,
        private OrderByService $orderByService
    ) {
    }

    #[Route('/admin/pathologies/{category}', name: 'admin_disease_kind_list', methods: ['GET'], defaults:['category' => 1])]
    public function adminList(
        PaginatorService $paginator,
        PaginatorPresenter $paginatorPresenter,
        Request $request,
        int $category
    ): Response {
        $query = $this->diseaseKindRepository->findDiseaseKindQuery($category);
        $diseaseKinds = $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);
        $paginatorPresenter->present($diseaseKinds, ['category' => (int) $category]);

        return $this->render('disease_kind/admin/list.html.twig', [
            'disease_kinds' => $diseaseKinds,
            'paginator' => $paginatorPresenter->viewModel(),
            'current_category' => $category,
        ]);
    }

    #[Route('/admin/pathologie/{diseaseKind}', name: 'admin_disease_kind_edit', methods: ['GET', 'POST'], defaults:['diseaseKind' => null])]
    public function adminEdit(
        Request $request,
        ?DiseaseKind $diseaseKind
    ): Response {
        $form = $this->createForm(DiseaseKindType::class, $diseaseKind);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $diseaseKind = $form->getData();

            if (-1 === $diseaseKind->getOrderBy() && null !== $diseaseKind->getCategory()) {
                $order = $this->diseaseKindRepository->findNexOrderByCategory($diseaseKind->getCategory());
                $diseaseKind->setOrderBy($order);
            }
            $this->entityManager->persist($diseaseKind);
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_disease_kind_list', [
                'category' => $diseaseKind->getCategory(),
            ]);
        }

        return $this->render('disease_kind/admin/edit.html.twig', [
            'disease_kind' => $diseaseKind,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/supprimer/pathologie/{diseaseKind}', name: 'admin_disease_kind_delete', methods: ['GET', 'POST'])]
    public function adminLevelDelete(
        Request $request,
        DiseaseKind $diseaseKind
    ): Response {
        $form = $this->createForm(FormType::class, null, [
            'action' => $this->generateUrl(
                'admin_disease_kind_delete',
                [
                    'diseaseKind' => $diseaseKind->getId(),
                ]
            ),
        ]);
        $category = $diseaseKind->getCategory();

        $form->handleRequest($request);
        if ($request->isMethod('post') && $form->isSubmitted() && $form->isValid()) {
            $diseaseKind->setDeleted(true)
                ->setOrderBy(-1);
            $this->entityManager->persist($diseaseKind);
            $this->entityManager->flush();

            $diseaseKinds = $this->diseaseKindRepository->findByCategory($category);
            $this->orderByService->ResetOrders($diseaseKinds);

            return $this->redirectToRoute('admin_disease_kind_list', [
                'category' => $category,
            ]);
        }

        return $this->render('disease_kind/admin/delete.modal.html.twig', [
            'disease_kind' => $diseaseKind,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/ordonner/pathologies/{diseaseKind}', name: 'admin_disease_kind_order', methods: ['POST'], options:['expose' => true])]
    public function adminLevelOrder(
        Request $request,
        DiseaseKind $diseaseKind
    ): Response {
        $category = $diseaseKind->getCategory();
        $newOrder = (int) $request->request->get('newOrder');
        $diseaseKinds = $this->diseaseKindRepository->findByCategory($category);

        $this->orderByService->setNewOrders($diseaseKind, $diseaseKinds, $newOrder);

        return new Response();
    }
}
