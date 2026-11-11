<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Core\Handler\ActionDirectHandler;
use App\Core\Handler\ActionFormHandler;
use App\Core\Handler\ListPaginatedHandler;
use App\Entity\Notification;
use App\Form\Admin\NotificationType;
use App\State\Notification\Processor\NotificationToggleProcessor;
use App\State\Notification\Processor\NotificationUpdateProcessor;
use App\State\Notification\Provider\NotificationAdminListProvider;
use App\State\Notification\Provider\NotificationToggleProvider;
use App\State\Notification\Provider\NotificationUpdateProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/popup', name: 'admin_notification_')]
class NotificationController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    #[IsGranted('MODAL_WINDOW_LIST')]
    public function list(
        Request $request,
        NotificationAdminListProvider $provider,
        ListPaginatedHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $provider,
        );
    }

    #[Route('/add', name: 'add', methods: ['GET', 'POST'])]
    #[IsGranted('MODAL_WINDOW_ADD')]
    public function add(
        Request $request,
        NotificationUpdateProvider $provider,
        NotificationUpdateProcessor $processor,
        ActionFormHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            new Notification(),
            $provider,
            $processor,
            NotificationType::class,
        );
    }

    #[Route('/edit/{notification}', name: 'edit', methods: ['GET', 'POST'], requirements:['notification' => '\d+'])]
    #[IsGranted('MODAL_WINDOW_EDIT', 'notification')]
    public function edit(
        Request $request,
        NotificationUpdateProvider $provider,
        NotificationUpdateProcessor $processor,
        Notification $notification,
        ActionFormHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $notification,
            $provider,
            $processor,
            NotificationType::class,
        );
    }

    #[Route('/toggle/{notification}', name: 'toggle', methods: ['GET', 'POST'])]
    #[IsGranted('MODAL_WINDOW_EDIT', 'notification')]
    public function toggle(
        Request $request,
        NotificationToggleProvider $provider,
        NotificationToggleProcessor $processor,
        Notification $notification,
        ActionDirectHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $notification,
            $provider,
            $processor
        );
    }
}
