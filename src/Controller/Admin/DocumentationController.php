<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Documentation;
use App\Form\Admin\DocumentationType;
use App\Repository\DocumentationRepository;
use App\Service\OrderByService;
use App\Service\PaginatorService;
use App\Service\UploadService;
use App\ViewModel\Documentation\DocumentationPresenter;
use App\ViewModel\Paginator\PaginatorPresenter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/documentation/', name: 'admin_documentation_')]
class DocumentationController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DocumentationRepository $documentationRepository,
        private OrderByService $orderByService,
        private DocumentationPresenter $documentationPresenter
    ) {
    }

    #[Route('list', name: 'list', methods: ['GET'])]
    public function adminList(
        PaginatorService $paginator,
        PaginatorPresenter $paginatorPresenter,
        Request $request,
    ): Response {
        $query = $this->documentationRepository->findDocumentationQuery();
        $documentations = $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);
        $paginatorPresenter->present($documentations);

        return $this->render('documentation/admin/list.html.twig', [
            'documentations' => $documentations,
            'paginator' => $paginatorPresenter->viewModel(),
        ]);
    }

    #[Route('editer/{documentation}', name: 'edit', methods: ['GET', 'POST'], defaults:['documentation' => null])]
    public function adminDocumentationEdit(
        Request $request,
        UploadService $uploadService,
        ?Documentation $documentation
    ): Response {
        $form = $this->createForm(DocumentationType::class, $documentation);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $documentation = $form->getData();
            if ($request->files->get('documentation')) {
                $file = $request->files->get('documentation')['file'];
                $documentation->setFileName($uploadService->uploadFile($file, 'documentation_directory_path'));
            }
            if (null === $documentation->getOrderBy()) {
                $order = $this->documentationRepository->findNexOrder();
                $documentation->setOrderBy($order);
            }
            $this->entityManager->persist($documentation);
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_documentation_list');
        }

        $this->documentationPresenter->present($documentation);
        return $this->render('documentation/admin/edit.html.twig', [
            'documentation' => $this->documentationPresenter->viewModel(),
            'form' => $form->createView(),
        ]);
    }

    #[Route('supprimer/{documentation}', name: 'delete', methods: ['GET', 'POST'])]
    public function adminDocumentationDelete(
        Request $request,
        Documentation $documentation
    ): Response {
        $form = $this->createForm(FormType::class, null, [
            'action' => $this->generateUrl(
                'admin_documentation_delete',
                [
                    'documentation' => $documentation->getId(),
                ]
            ),
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('post') && $form->isSubmitted() && $form->isValid()) {
            $this->entityManager->remove($documentation);
            $this->entityManager->flush();

            $documentations = $this->documentationRepository->findAll();
            $this->orderByService->ResetOrders($documentations);

            return $this->redirectToRoute('admin_documentation_list');
        }

        return $this->render('documentation/admin/delete.modal.html.twig', [
            'documentation' => $documentation,
            'form' => $form->createView(),
        ]);
    }

    #[Route('ordonner/{documentation}', name: 'order', methods: ['POST'], options:['expose' => true])]
    public function adminDocumentationOrder(
        Request $request,
        Documentation $documentation
    ): Response {
        $newOrder = (int) $request->request->get('newOrder');
        $documentations = $this->documentationRepository->findAll();

        $this->orderByService->setNewOrders($documentation, $documentations, $newOrder);

        return new Response();
    }
}