<?php

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\PaginatorDtoTransformer;
use App\Entity\BoardRole;
use App\Form\Admin\BoardRoleType;
use App\Repository\BoardRoleRepository;
use App\Repository\UserRepository;
use App\Service\OrderByService;
use App\Service\PaginatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
        $boardRoles = $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);

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
        UserRepository $userRepository,
        BoardRole $boardRole
    ): Response {
        $form = $this->createForm(FormType::class, null, [
            'action' => $this->generateUrl(
                'admin_board_role_delete',
                [
                    'boardRole' => $boardRole->getId(),
                ]
            ),
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('post') && $form->isSubmitted() && $form->isValid()) {
            $userRepository->removeBoardRole($boardRole);
            $this->entityManager->remove($boardRole);
            $this->entityManager->flush();

            $boardRoles = $this->boardRoleRepository->findAllOrdered();
            $this->orderByService->ResetOrders($boardRoles);

            return $this->redirectToRoute('admin_board_role_list');
        }

        return $this->render('board_role/admin/delete.modal.html.twig', [
            'boardRole' => $boardRole,
            'form' => $form->createView(),
        ]);
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
