<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\MessageDtoTransformer;
use App\Dto\DtoTransformer\PaginatorDtoTransformer;
use App\Entity\Message;
use App\Entity\Section;
use App\Form\Admin\MessageFilterType;
use App\Form\Admin\MessageType;
use App\Repository\MessageRepository;
use App\Repository\SectionRepository;
use App\Service\PaginatorService;
use App\State\Message\Processor\MessageDeleteProcessor;
use App\State\Message\Processor\MessageUpdateProcessor;
use App\State\Message\Provider\MessageDeleteProvider;
use App\State\Message\Provider\MessageUpdateProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/message', name: 'admin_message_')]
class MessageController extends AbstractCrudController
{
    public function __construct(
        private readonly MessageRepository $messageRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/liste/{section}', name: 'list', methods: ['GET', 'POST'], defaults:['section' => null])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminList(
        PaginatorService $paginator,
        MessageDtoTransformer $messageDtoTransformer,
        PaginatorDtoTransformer $paginatorDtoTransformer,
        Request $request,
        ?Section $section
    ): Response {
        $form = $this->createForm(MessageFilterType::class, ['section' => $section]);
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $section = $form->get('section')->getData();
        }
        $query = $this->messageRepository->findMessageQuery($section);
        $messages = $paginator->paginateFromRequest($query, $request, PaginatorService::PAGINATOR_PER_PAGE);
        return $this->render('message/admin/list.html.twig', [
            'form' => $form->createView(),
            'messages' => $messageDtoTransformer->fromEntities($messages),
            'paginator' => $paginatorDtoTransformer->fromEntities($messages, ['section' => $section?->getId()]),
        ]);
    }

    #[Route('/nouveau/{sectionId}', name: 'add', defaults: ['sectionId' => null], methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminAdd(
        Request $request,
        SectionRepository $sectionRepository,
        ?string $sectionId
    ): Response {
        $message = new Message();
        $section = null;
        if ($sectionId) {
            $section = $sectionRepository->findOneById($sectionId);
            $message->setSection($section);
        }
        $form = $this->createForm(MessageType::class, $message, [
            'referer' => $request->headers->get('referer'),
            'full_mode' => true,
        ]);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $message = $form->getData();
            $this->entityManager->persist($message);
            $this->entityManager->flush();

            return $this->redirect($request->request->all('message')['referer']);
        }

        return $this->render('message/admin/edit.html.twig', [
            'message' => null,
            'sectionId' => $section?->getId(),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/content/{message}', name: 'edit_content', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminEditContent(
        Request $request,
        MessageUpdateProvider $provider,
        MessageUpdateProcessor $processor,
        Message $message
    ): Response {
        return $this->handleFormComponentAction(
            $request,
            $message,
            $provider,
            $processor,
            MessageType::class
        );
    }

    #[Route('/{message}', name: 'edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminEdit(
        Request $request,
        Message $message
    ): Response {
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_message_list', [
                'section' => $message->getSection(),
            ]);
        }

        return $this->render('message/admin/edit.html.twig', [
            'message' => $message,
            'sectionId' => $message->getSection()?->getId(),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/supprimer/{message}', name: 'delete', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminLevelDelete(
        Request $request,
        MessageDeleteProcessor $processor,
        MessageDeleteProvider $provider,
        Message $message
    ): Response {
        return $this->handleFormComponentAction(
            $request,
            $message,
            $provider,
            $processor
        );
    }

    #[Route('/autocomplete', name: 'autocomplete', methods: ['GET'])]
    public function autocomplete(
        Request $request,
    ): JsonResponse {
        $query = $request->query->get('q');
        $results = [];
        $messages = $this->messageRepository->findBySectionNameAndQuery('BIKE_RIDE_TYPE', $query);
        foreach ($messages as $message) {
            $results[] = [
                'value' => $message->getId(),
                'text' => $message->__toString(),
            ];
        }

        return new JsonResponse(['results' => $results]);
    }
}
