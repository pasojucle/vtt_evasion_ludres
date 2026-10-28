<?php

namespace App\Controller\Admin;

use App\Core\Handler\ActionDialogHandler;
use App\Core\Handler\ActionDirectHandler;
use App\Core\Handler\ActionFormHandler;
use App\Core\Handler\ActionJsonHandler;
use App\Core\Handler\ListPaginatedHandler;
use App\Dto\Payload\SortablePayload;
use App\Entity\BoardRole;
use App\Form\Admin\BoardRoleType;
use App\State\BoardRole\Processor\BoardRoleDeleteProcessor;
use App\State\BoardRole\Processor\BoardRoleOrderProcessor;
use App\State\BoardRole\Processor\BoardRoleRestoreProcessor;
use App\State\BoardRole\Processor\BoardRoleUpdateProcessor;
use App\State\BoardRole\Provider\BoardRoleDeleteProvider;
use App\State\BoardRole\Provider\BoardRoleListProvider;
use App\State\BoardRole\Provider\BoardRoleRestoreProvider;
use App\State\BoardRole\Provider\BoardRoleUpdateProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/param/bureau/role', name: 'admin_board_role')]
class BoardRoleController extends AbstractController
{
    #[Route('s', name: '_list', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminBoardRoleList(
        Request $request,
        BoardRoleListProvider $provider,
        ListPaginatedHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $provider,
        );
    }

    #[Route('/add', name: '_add', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminBoardRoleAdd(
        Request $request,
        BoardRoleUpdateProvider $provider,
        BoardRoleUpdateProcessor $processor,
        ActionFormHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            new BoardRole(),
            $provider,
            $processor,
            BoardRoleType::class
        );
    }

    #[Route('/edit/{boardRole}', name: '_edit', requirements:['boardRole' => '\d+'], methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminBoardRoleEdit(
        Request $request,
        BoardRoleUpdateProvider $provider,
        BoardRoleUpdateProcessor $processor,
        BoardRole $boardRole,
        ActionFormHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $boardRole,
            $provider,
            $processor,
            BoardRoleType::class
        );
    }

    #[Route('/supprimer/{boardRole}', name: '_delete', requirements:['boardRole' => '\d+'], methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminBoardRoleDelete(
        Request $request,
        BoardRoleDeleteProvider $provider,
        BoardRoleDeleteProcessor $processor,
        BoardRole $boardRole,
        ActionDialogHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $boardRole,
            $provider,
            $processor
        );
    }

    #[Route('/ordonner/{boardRole}', name: '_order', requirements:['boardRole' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminBoardRoleOrder(
        Request $request,
        BoardRole $boardRole,
        BoardRoleOrderProcessor $processor,
        ActionJsonHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            SortablePayload::fromRequest($boardRole, $request),
            $processor
        );
    }

    #[Route('/restaure/{boardRole}', name: '_restore', requirements:['boardRole' => '\d+'], methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminLevelRestore(
        Request $request,
        BoardRoleRestoreProvider $provider,
        BoardRoleRestoreProcessor $processor,
        BoardRole $boardRole,
        ActionDirectHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $boardRole,
            $provider,
            $processor
        );
    }
}
