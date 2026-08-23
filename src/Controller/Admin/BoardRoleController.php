<?php

namespace App\Controller\Admin;

use App\Dto\Filter\BoardRoleFilter;
use App\Entity\BoardRole;
use App\Form\Admin\BoardRoleType;
use App\Repository\BoardRoleRepository;
use App\Service\OrderByService;
use App\State\BoardRole\Processor\BoardRoleDeleteProcessor;
use App\State\BoardRole\Processor\BoardRoleRestoreProcessor;
use App\State\BoardRole\Provider\BoardRoleDeleteProvider;
use App\State\BoardRole\Provider\BoardRoleListProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/param/bureau/role', name: 'admin_board_role')]
class BoardRoleController extends AbstractCrudController
{
    public function __construct(private BoardRoleRepository $boardRoleRepository, private EntityManagerInterface $entityManager, private OrderByService $orderByService)
    {
    }

    #[Route('s', name: '_list', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminBoardRoleList(
        BoardRoleListProvider $provider,
        Request $request
    ): Response {
        return $this->handleListPaginedAction(
            BoardRoleFilter::class,
            $provider,
            $request
        );
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
        return $this->handleFormComponentAction(
            $request,
            $boardRole,
            $provider,
            $processor
        );
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

    #[Route('/restaure/{boardRole}', name: '_restore', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminLevelRestore(
        Request $request,
        BoardRoleRestoreProcessor $processor,
        BoardRole $boardRole
    ): Response {
        return $this->handleProcessAction(
            $request,
            $boardRole,
            $processor
        );
    }
}
