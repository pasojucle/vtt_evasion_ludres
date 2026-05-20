<?php

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\PaginatorDtoTransformer;
use App\Entity\BoardRole;
use App\Form\Admin\BoardRoleType;
use App\Repository\BoardRoleRepository;
use App\Repository\MemberRepository;
use App\Service\OrderByService;
use App\Service\PaginatorService;
use App\State\BoardRole\Processor\BoardRoleDeleteProcessor;
use App\State\BoardRole\Provider\BoardRoleDeleteProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/param/bureau/role', name: 'admin_board_role')]
class BoardRoleController extends AbstractController
{
    public function __construct(private BoardRoleRepository $boardRoleRepository, private EntityManagerInterface $entityManager, private OrderByService $orderByService)
    {
    }

    #[Route('s', name: '_list', methods: ['GET'], defaults:['type' => 1])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminBoardRoleList(
        PaginatorService $paginator,
        PaginatorDtoTransformer $paginatorDtoTransformer,
        Request $request
    ): Response {
        $query = $this->boardRoleRepository->findBoardRoleQuery();
        $boardRoles = $paginator->paginateFromRequest($query, $request, PaginatorService::PAGINATOR_PER_PAGE);

        return $this->render('board_role/admin/list.html.twig', [
            'boardRoles' => $boardRoles,
            'paginator' => $paginatorDtoTransformer->fromEntities($boardRoles),
        ]);
    }

    #[Route('/{boardRole}', name: '_edit', methods: ['GET', 'POST'], defaults:['boardRole' => null])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminBoardRoleEdit(
        Request $request,
        ?BoardRole $boardRole
    ): Response {
        $form = $this->createForm(BoardRoleType::class, $boardRole);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $boardRole = $form->getData();

            if (null === $boardRole->getOrderBy()) {
                $order = $this->boardRoleRepository->findNexOrder();
                $boardRole->setOrderBy($order);
            }
            $this->entityManager->persist($boardRole);
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_board_role_list');
        }

        return $this->render('board_role/admin/edit.html.twig', [
            'boardRole' => $boardRole,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/supprimer/{boardRole}', name: '_delete', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminBoardRoleDelete(
        Request $request,
        BoardRoleDeleteProcessor $processor,
        BoardRoleDeleteProvider $provider,
        BoardRole $boardRole
    ): Response {
        $response = new Response("OK", Response::HTTP_OK);
        $form = $this->createForm(FormType::class, null, [
            'action' => $request->getUri(),
            'attr' => ['data-action' => 'turbo:submit-end->modal#handleFormSubmit']
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted()) {
            if ($form->isValid()) {
                $processor->process($boardRole);

                return $this->redirectToRoute('admin_board_role_list');
            }
            $response = new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->render('component/_dialog.modal.html.twig', [
            'form' => $form->createView(),
            'dialog' => $provider->mapToView($boardRole),
        ], $response);
    }

    #[Route('/ordonner/{boardRole}', name: '_order', methods: ['POST'], options:['expose' => true])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminBoardRoleOrder(
        Request $request,
        BoardRole $boardRole
    ): Response {
        $newOrder = (int) $request->request->get('newOrder');
        $boardRoles = $this->boardRoleRepository->findAllOrdered();

        $this->orderByService->setNewOrders($boardRole, $boardRoles, $newOrder);

        return new Response();
    }
}
