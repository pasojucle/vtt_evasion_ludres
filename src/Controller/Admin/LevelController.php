<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Core\Handler\ActionDirectHandler;
use App\Core\Handler\ActionFormHandler;
use App\Core\Handler\ActionJsonHandler;
use App\Core\Handler\ListPaginatedHandler;
use App\Dto\Payload\LevelOrderPayload;
use App\Entity\Level;
use App\Form\Admin\LevelType;
use App\State\Level\Processor\LevelDeleteProcessor;
use App\State\Level\Processor\LevelOrderProcessor;
use App\State\Level\Processor\LevelRestoreProcessor;
use App\State\Level\Processor\LevelUpdateProcessor;
use App\State\Level\Provider\LevelDeleteProvider;
use App\State\Level\Provider\LevelListProvider;
use App\State\Level\Provider\LevelRestoreProvider;
use App\State\Level\Provider\LevelUpdateProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/param/niveau', name: 'admin_level_')]
class LevelController extends AbstractController
{
    #[Route('x', name: 'list', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminList(
        Request $request,
        LevelListProvider $provider,
        ListPaginatedHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $provider,
        );
    }

    #[Route('/add', name: 'add', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminLevelAdd(
        Request $request,
        LevelUpdateProvider $provider,
        LevelUpdateProcessor $processor,
        ActionFormHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            new Level(),
            $provider,
            $processor,
            LevelType::class
        );
    }

    #[Route('/edit/{level}', name: 'edit', methods: ['GET', 'POST'], requirements:['level' => '\d+'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminLevelEdit(
        Request $request,
        LevelUpdateProvider $provider,
        LevelUpdateProcessor $processor,
        Level $level,
        ActionFormHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $level,
            $provider,
            $processor,
            LevelType::class
        );
    }

    #[Route('/supprimer/{level}', name: 'delete', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminLevelDelete(
        Request $request,
        LevelDeleteProvider $provider,
        LevelDeleteProcessor $processor,
        Level $level,
        ActionFormHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $level,
            $provider,
            $processor
        );
    }

    #[Route('/restaure/{level}', name: 'restore', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminLevelRestore(
        Request $request,
        LevelRestoreProvider $provider,
        LevelRestoreProcessor $processor,
        Level $level,
        ActionDirectHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $level,
            $provider,
            $processor
        );
    }

    #[Route('/ordonner/{level}', name: 'order', requirements:['level' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminLevelOrder(
        Request $request,
        LevelOrderProcessor $processor,
        Level $level,
        ActionJsonHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            LevelOrderPayload::fromRequest($level, $request),
            $processor
        );
    }
}
