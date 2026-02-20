<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\DtoTransformer\PaginatorDtoTransformer;
use App\Dto\DtoTransformer\SecondHandDtoTransformer;
use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\SecondHand;
use App\Entity\User;
use App\Form\SecondHandType;
use App\Repository\ContentRepository;
use App\Repository\SecondHandRepository;
use App\Service\LogService;
use App\Service\MailerService;
use App\Service\MessageService;
use App\Service\PaginatorService;
use App\UseCase\SecondHand\EditSecondHand;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(name: 'second_hand_')]
class SecondHandController extends AbstractController
{
    public function __construct(
        private SecondHandDtoTransformer $secondHandDtoTransformer,
        private SecondHandRepository $secondHandRepository,
    ) {
    }

    #[Route('/occasions', name: 'list', methods: ['GET', 'POST'])]
    #[IsGranted('SECOND_HAND_LIST')]
    public function list(
        PaginatorService $paginator,
        PaginatorDtoTransformer $paginatorDtoTransformer,
        Request $request,
    ): Response {
        /** @var ?User $user */
        $user = $this->getUser();
        $novelties = $this->secondHandRepository->findNoveltiesByUserIds($user);
        $query = $this->secondHandRepository->findSecondHandEnabledQuery();
        $secondHands = $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);
        return $this->render('second_hand/list.html.twig', [
            'second_hands' => $this->secondHandDtoTransformer->fromEntities($secondHands, $novelties),
            'paginator' => $paginatorDtoTransformer->fromEntities($secondHands),
        ]);
    }

    #[Route('/occasion/detail/{secondHand}', name: 'show', methods: ['GET'])]
    #[IsGranted('SECOND_HAND_VIEW', 'secondHand')]
    public function show(
        LogService $logService,
        SecondHand $secondHand
    ): Response {
        $logService->writeFromEntity($secondHand);
        return $this->render('second_hand/show.html.twig', [
            'second_hand' => $this->secondHandDtoTransformer->fromEntity($secondHand),
        ]);
    }



    #[Route('/mon-compte/occasions', name: 'user_list', methods: ['GET', 'POST'])]
    #[IsGranted('SECOND_HAND_LIST')]
    public function userList(?SecondHand $secondHand): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('second_hand/user_list.html.twig', [
            'second_hands' => $this->secondHandDtoTransformer->fromEntities($user->getSecondHands()),
        ]);
    }



    #[Route('/mon-compte/occasion', name: 'add', methods: ['GET', 'POST'])]
    #[IsGranted('SECOND_HAND_ADD')]
    public function add(
        Request $request,
        ContentRepository $contentRepository,
        EditSecondHand $editSecondHand
    ): Response {
        $secondHand = null;
        $form = $this->createForm(SecondHandType::class, new SecondHand(), [
            'action' => $this->generateUrl('second_hand_add')
        ]);
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $secondHand = $form->getData();
            $editSecondHand->execute($form, $request);
            return $this->redirectToRoute('second_hand_user_list');
        }

        return $this->render('second_hand/edit.html.twig', [
            'second_hand' => $this->secondHandDtoTransformer->fromEntity($secondHand),
            'form' => $form->createView(),
            'content' => $contentRepository->findOneByRoute('second_hand'),
        ]);
    }

    #[Route('/mon-compte/occasion/{secondHand}', name: 'edit', requirements:['secondHand' => '\d+'], methods: ['GET', 'POST'])]
    #[IsGranted('SECOND_HAND_EDIT', 'secondHand')]
    public function edit(
        Request $request,
        SecondHand $secondHand,
        ContentRepository $contentRepository,
        EditSecondHand $editSecondHand
    ): Response {
        $form = $this->createForm(SecondHandType::class, $secondHand, [
            'action' => $this->generateUrl('second_hand_edit', [
                'secondHand' => $secondHand->getId(),
            ])
        ]);
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $editSecondHand->execute($form, $request);
            return $this->redirectToRoute('second_hand_user_list');
        }

        return $this->render('second_hand/edit.html.twig', [
            'second_hand' => $this->secondHandDtoTransformer->fromEntity($secondHand),
            'form' => $form->createView(),
            'content' => $contentRepository->findOneByRoute('second_hand'),
        ]);
    }

    #[Route('/occasion/delete/{secondHand}', name: 'delete', methods: ['GET', 'POST'])]
    #[IsGranted('SECOND_HAND_EDIT', 'secondHand')]
    public function delete(
        Request $request,
        SecondHand $secondHand
    ): Response {
        $response = new Response("OK", Response::HTTP_OK);
        $form = $this->createForm(FormType::class, null, [
            'action' => $request->getUri(),
            'attr' => ['data-action' => 'turbo:submit-end->modal#handleFormSubmit']
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted()) {
            if ($form->isValid()) {
                $this->secondHandRepository->remove($secondHand, true);

                return $this->redirectToRoute('second_hand_user_list');
            }
            $response = new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->render('component/destructive.modal.html.twig', [
            'title' => 'Supprimer une annonce',
            'content' => sprintf('Etes vous certain de supprimer l\'annonce %s ?', $secondHand->getName()),
            'btn_label' => 'Supprimer',
            'form' => $form->createView(),
        ], $response);
    }

    #[Route('/occasion/enabled/{secondHand}', name: 'enabled', methods: ['GET'])]
    #[IsGranted('SECOND_HAND_EDIT', 'secondHand')]
    public function enabled(
        SecondHand $secondHand
    ): Response {
        $secondHand->setDisabled(false);
        $this->secondHandRepository->save($secondHand, true);

        return $this->redirectToRoute('second_hand_user_list');
    }

    #[Route('/occasion/contact/{secondHand}', name: 'message', methods: ['GET', 'POST'])]
    #[IsGranted('SECOND_HAND_VIEW', 'secondHand')]
    public function contact(
        MailerService $mailerService,
        UserDtoTransformer $userDtoTransformer,
        MessageService $messageService,
        Request $request,
        SecondHand $secondHand
    ): Response {
        $form = $this->createForm(FormType::class, null, [
            'action' => $this->generateUrl(
                'second_hand_message',
                ['secondHand' => $secondHand->getId()]
            ),
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            /** @var ?User $buyer */
            $buyer = $this->getUser();
            $buyerDto = $userDtoTransformer->identifiersFromEntity($buyer);
            $content = $messageService->getMessageByName('SECOND_HAND_CONTACT');
            $additionalParams = [
                '{{ nom_annonce }}' => $secondHand->getName(),
                '{{ telephone }}' => $buyerDto->member->phone,
                '{{ email }}' => $buyerDto->mainEmail,
                '{{ prenom_nom }}' => $buyerDto->member->fullName,
            ];
            $seller = $secondHand->getUser();
            $sellerDto = $userDtoTransformer->identifiersFromEntity($seller);
            $subject = sprintf('Votre annonce %s', $secondHand->GetName());
            
            if ($mailerService->sendMailToMember($sellerDto, $subject, $content, null, $additionalParams)) {
                $this->addFlash('success', 'Votre message a bien été envoyé');
            } else {
                $this->addFlash('danger', 'Une erreure est survenue');
            }

            return $this->redirectToRoute('second_hand_list');
        }

        return $this->render('second_hand/contact.modal.html.twig', [
            'message' => $messageService->getMessageByName('SECOND_HAND_CONTACT_CONFIRM'),
            'form' => $form->createView(),
        ]);
    }
}
