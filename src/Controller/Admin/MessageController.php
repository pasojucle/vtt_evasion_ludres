<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Core\Handler\ActionDialogHandler;
use App\Core\Handler\AutocompleteHandler;
use App\Core\Handler\ListPaginatedHandler;
use App\Dto\Filter\MessageAutocompleteFilter;
use App\Entity\Message;
use App\Form\Admin\MessageContentType;
use App\Form\Admin\MessageType;
use App\State\Message\Processor\MessageDeleteProcessor;
use App\State\Message\Processor\MessageUpdateProcessor;
use App\State\Message\Provider\MessageAutocompleteProvider;
use App\State\Message\Provider\MessageDeleteProvider;
use App\State\Message\Provider\MessageListProvider;
use App\State\Message\Provider\MessageUpdateContentProvider;
use App\State\Message\Provider\MessageUpdateProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/message', name: 'admin_message_')]
class MessageController extends AbstractController
{
    #[Route('/liste', name: 'list', methods: ['GET', 'POST'], defaults:['section' => null])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminList(
        Request $request,
        MessageListProvider $provider,
        ListPaginatedHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $provider,
        );
    }

    #[Route('/nouveau/{sectionId}', name: 'add', defaults: ['sectionId' => null], methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminAdd(
        Request $request,
        MessageUpdateProvider $provider,
        MessageUpdateProcessor $processor,
        ActionDialogHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            new Message(),
            $provider,
            $processor,
            MessageType::class
        );
    }

    #[Route('/content/{message}', name: 'edit_content', requirements:['message' => '\w+'], methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminEditContent(
        Request $request,
        MessageUpdateContentProvider $provider,
        MessageUpdateProcessor $processor,
        Message $message,
        ActionDialogHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $message,
            $provider,
            $processor,
            MessageContentType::class
        );
    }

    #[Route('/edit/{message}', name: 'edit', requirements:['message' => '\w+'], methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminEdit(
        Request $request,
        MessageUpdateProvider $provider,
        MessageUpdateProcessor $processor,
        Message $message,
        ActionDialogHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $message,
            $provider,
            $processor,
            MessageType::class
        );
    }

    #[Route('/supprimer/{message}', name: 'delete', requirements:['message' => '\w+'], methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminLevelDelete(
        Request $request,
        MessageDeleteProvider $provider,
        MessageDeleteProcessor $processor,
        Message $message,
        ActionDialogHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $message,
            $provider,
            $processor
        );
    }

    #[Route('/autocomplete', name: 'autocomplete', methods: ['GET'])]
    public function autocomplete(
        Request $request,
        MessageAutocompleteProvider $provider,
        AutocompleteHandler $handler,
    ): JsonResponse {
        return $handler->handle(
            $request,
            $provider,
            MessageAutocompleteFilter::class,
        );
    }
}
