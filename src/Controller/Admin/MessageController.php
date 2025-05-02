<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\MessageDtoTransformer;
use App\Dto\DtoTransformer\PaginatorDtoTransformer;
use App\Entity\Message;
use App\Entity\ParameterGroup;
use App\Form\Admin\MessageFilterType;
use App\Form\Admin\MessageType;
use App\Repository\MessageRepository;
use App\Repository\ParameterGroupRepository;
use App\Service\ApiService;
use App\Service\PaginatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/message', name: 'admin_message_')]
class MessageController extends AbstractController
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
        ?ParameterGroup $section
    ): Response {
        $form = $this->createForm(MessageFilterType::class, ['section' => $section]);
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $section = $form->get('section')->getData();
        }
        $query = $this->messageRepository->findMessageQuery($section);
        $messages = $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);
        return $this->render('message/admin/list.html.twig', [
            'form' => $form->createView(),
            'messages' => $messageDtoTransformer->fromEntities($messages),
            'paginator' => $paginatorDtoTransformer->fromEntities($messages, ['section' => $section?->getId()]),
        ]);
    }

    #[Route('/nouveau/{sectionName}', name: 'add', defaults: ['sectionName' => null], methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminAdd(
        Request $request,
        ParameterGroupRepository $parameterGroupRepository,
        ?string $sectionName
    ): Response {
        $message = new Message();
        $section = null;
        if ($sectionName) {
            $section = $parameterGroupRepository->findOneByName($sectionName);
            $message->setSection($section);
        }
        $form = $this->createForm(MessageType::class, $message, [
            'referer' => $request->headers->get('referer'),
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

    #[Route('/content/{message}', name: 'edit_content', methods: ['GET', 'POST'], requirements:['message' => '\d+'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminEditContent(
        Request $request,
        Message $message
    ): Response {
        $form = $this->createForm(MessageType::class, $message, [
            'action' => $this->generateUrl($request->attributes->get('_route'), $request->attributes->get('_route_params'), ),
            'referer' => $request->headers->get('referer'),
            'modal' => true,
        ]);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            return $this->redirect($request->request->all('message')['referer']);
        }

        return $this->render('message/admin/edit.modal.html.twig', [
            'message' => $message,
            'section' => $message->getSection(),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/content/react/{message}', name: 'edit_content_react', methods: ['GET', 'POST'], requirements:['message' => '\d+'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminEditContentReact(
        Request $request,
        ApiService $api,
        Message $message
    ): Response {
        $form = $this->createForm(MessageType::class, $message, [
            'action' => $this->generateUrl($request->attributes->get('_route'), $request->attributes->get('_route_params'), ),
            'referer' => $request->headers->get('referer'),
            'modal' => true,
        ]);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            return new JsonResponse(['success' => true]);
        }

        return $api->renderModal($form, 'Modifier un message', 'Modifier');
    }

    #[Route('/{message}', name: 'edit', methods: ['GET', 'POST'], requirements:['message' => '\d+'])]
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
        Message $message
    ): Response {
        $form = $this->createForm(FormType::class, null, [
            'action' => $this->generateUrl('admin_message_delete', [
                'message' => $message->getId(),
            ]),
        ]);
        $section = $message->getSection();
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $this->entityManager->remove($message);
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_message_list', [
                'section' => $section,
            ]);
        }

        return $this->render('message/admin/delete.modal.html.twig', [
            'message' => $message,
            'form' => $form->createView(),
        ]);
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
