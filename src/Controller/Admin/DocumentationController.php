<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\DocumentationDtoTransformer;
use App\Dto\DtoTransformer\PaginatorDtoTransformer;
use App\Entity\Documentation;
use App\Form\Admin\DocumentationType;
use App\Repository\DocumentationRepository;
use App\Service\MessageService;
use App\Service\OrderByService;
use App\Service\PaginatorService;
use App\UseCase\Documentation\EditDocumentation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/documentation', name: 'admin_documentation_')]
class DocumentationController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DocumentationRepository $documentationRepository,
        private OrderByService $orderByService,
        private DocumentationDtoTransformer $documentationDtoTransformer
    ) {
    }

    #[Route('s', name: 'list', methods: ['GET'])]
    #[IsGranted('DOCUMENTATION_LIST')]
    public function adminList(
        PaginatorService $paginator,
        PaginatorDtoTransformer $paginatorDtoTransformer,
        MessageService $messageService,
        Request $request,
    ): Response {
        $query = $this->documentationRepository->findDocumentationQuery();
        $documentations = $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);

        return $this->render('documentation/admin/list.html.twig', [
            'documentations' => $documentations,
            'paginator' => $paginatorDtoTransformer->fromEntities($documentations),
            'settings' => [
                'messages' => $messageService->getMessagesBySectionName('DOCUMENTATION'),
            ],
        ]);
    }

    #[Route('/', name: 'add', methods: ['GET', 'POST'])]
    #[IsGranted('DOCUMENTATION_ADD')]
    public function add(
        Request $request,
        EditDocumentation $editDocumentation
    ): Response {
        $documentation = new Documentation();
        $form = $this->createForm(DocumentationType::class, $documentation);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $documentation = $editDocumentation->execute($form, $request, true);

            return $this->redirectToRoute('admin_documentation_list');
        }

        return $this->render('documentation/admin/edit.html.twig', [
            'documentation' => $this->documentationDtoTransformer->fromEntity($documentation),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/editer/{documentation}', name: 'edit', methods: ['GET', 'POST'], requirements:['documentation' => '\d+'])]
    #[IsGranted('DOCUMENTATION_EDIT', 'documentation')]
    public function adminDocumentationEdit(
        Request $request,
        EditDocumentation $editDocumentation,
        Documentation $documentation
    ): Response {
        $form = $this->createForm(DocumentationType::class, $documentation);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $documentation = $editDocumentation->execute($form, $request, true);

            return $this->redirectToRoute('admin_documentation_list');
        }

        return $this->render('documentation/admin/edit.html.twig', [
            'documentation' => $this->documentationDtoTransformer->fromEntity($documentation),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/supprimer/{documentation}', name: 'delete', methods: ['GET', 'POST'])]
    #[IsGranted('DOCUMENTATION_EDIT', 'documentation')]
    public function adminDocumentationDelete(
        Request $request,
        Documentation $documentation
    ): Response {
        $response = new Response("OK", Response::HTTP_OK);
        $form = $this->createForm(FormType::class, null, [
            'action' => $request->getUri(),
            'attr' => ['data-action' => 'turbo:submit-end->modal#handleFormSubmit']
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted()) {
            if ($form->isValid()) {
                $this->entityManager->remove($documentation);
                $this->entityManager->flush();

                $documentations = $this->documentationRepository->findAll();
                $this->orderByService->ResetOrders($documentations);

                return $this->redirectToRoute('admin_documentation_list');
            }
            $response = new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->render('component/destructive.modal.html.twig', [
            'form' => $form->createView(),
            'title' => 'Supprimer une documentation',
            'content' => sprintf('Etes vous certain de supprimer la documentation %s', $documentation->getName()),
            'btn_label' => 'Supprimer',
        ], $response);
    }

    #[Route('/ordonner/{documentation}', name: 'order', methods: ['POST'], options:['expose' => true])]
    #[IsGranted('DOCUMENTATION_EDIT', 'documentation')]
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
