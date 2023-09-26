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
use App\Repository\ParameterRepository;
use App\Repository\SecondHandRepository;
use App\Service\MailerService;
use App\Service\PaginatorService;
use App\UseCase\SecondHand\EditSecondHand;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('occasion', name: 'second_hand_')]
class SecondHandController extends AbstractController
{
    public function __construct(
        private SecondHandDtoTransformer $secondHandDtoTransformer,
        private SecondHandRepository $secondHandRepository,
    ) {
    }

    #[Route('/list', name: 'list', methods: ['GET', 'POST'])]
    public function list(
        PaginatorService $paginator,
        PaginatorDtoTransformer $paginatorDtoTransformer,
        Request $request,
    ): Response {
        $query = $this->secondHandRepository->findSecondHandEnabled();
        $secondHands = $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);
        return $this->render('second_hand/list.html.twig', [
            'second_hands' => $this->secondHandDtoTransformer->fromEntities($secondHands),
            'paginator' => $paginatorDtoTransformer->fromEntities($secondHands),
        ]);
    }


    #[Route('/detail/{secondHand}', name: 'show', methods: ['GET'])]
    public function show(SecondHand $secondHand): Response
    {
        return $this->render('second_hand/show.html.twig', [
            'second_hand' => $this->secondHandDtoTransformer->fromEntity($secondHand),
        ]);
    }



    #[Route('/mes_occasions', name: 'user_list', methods: ['GET', 'POST'])]
    public function userList(?SecondHand $secondHand): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('second_hand/user_list.html.twig', [
            'second_hands' => $this->secondHandDtoTransformer->fromEntities($user->getSecondHands()),
        ]);
    }



    #[Route('/edit/{secondHand}', name: 'edit', defaults: ['secondHand' => null], methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        ?SecondHand $secondHand,
        ContentRepository $contentRepository,
        EditSecondHand $editSecondHand
    ): Response {
        $form = $this->createForm(SecondHandType::class, $secondHand, [
            'action' => $this->generateUrl('second_hand_edit', [
                'secondHand' => $secondHand?->getId(),
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

    #[Route('/delete/{secondHand}', name: 'delete', methods: ['GET', 'POST'])]
    public function delete(
        Request $request,
        SecondHand $secondHand
    ): Response {
        $form = $this->createForm(FormType::class, null, [
            'action' => $this->generateUrl(
                'second_hand_delete',
                ['secondHand' => $secondHand->getId(), ]
            ),
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('post') && $form->isSubmitted() && $form->isValid()) {
            $this->secondHandRepository->remove($secondHand, true);

            return $this->redirectToRoute('second_hand_user_list');
        }

        return $this->render('second_hand/delete.modal.html.twig', [
            'second_hand' => $this->secondHandDtoTransformer->fromEntity($secondHand),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/enabled/{secondHand}', name: 'enabled', methods: ['GET'])]
    public function enabled(
        SecondHand $secondHand
    ): Response {
        $secondHand->setDisabled(false);
        $this->secondHandRepository->save($secondHand, true);

        return $this->redirectToRoute('second_hand_user_list');
    }

    #[Route('/contact/{secondHand}', name: 'message', methods: ['GET', 'POST'])]
    public function contact(
        MailerService $mailerService,
        Request $request,
        UserDtoTransformer $userDtoTransformer,
        ContentRepository $contentRepository,
        ParameterRepository $parameterRepository,
        SecondHand $secondHand
    ): Response {
        
        /** @var ?User $buyer */
        $buyer = $this->getUser();
        $buyerDto = $userDtoTransformer->fromEntity($buyer);
        $content = $parameterRepository->findOneByName('second_hand_contact');
        $search = ['{{ nom_annonce }}', '{{ telephone }}', '{{ email }}', '{{ prenom_nom }}'];
        $replace = [$secondHand->getName(), $buyerDto->member->phone, $buyerDto->mainEmail, $buyerDto->member->fullName];
        $seller = $secondHand->getUser();
        $sellerDto = $userDtoTransformer->fromEntity($seller);
        $data = [
            'name' => $sellerDto->member->name,
            'firstName' => $sellerDto->member->firstName,
            'email' => $sellerDto->mainEmail,
            'subject' => sprintf('Votre annonce %s', $secondHand->GetName()),
            'message' => str_replace($search, $replace, $content->getValue()),
        ]; 
        
        if ($mailerService->sendMailToMember($data)) {
            $this->addFlash('success', 'Votre message a bien été envoyé');
        }
        
        $this->addFlash('danger', 'Une erreure est survenue');
        return $this->redirectToRoute('second_hand_show', ['secondHand' => $secondHand->getId()]);
    }
}
