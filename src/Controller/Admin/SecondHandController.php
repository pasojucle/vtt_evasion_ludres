<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\PaginatorDtoTransformer;
use App\Dto\DtoTransformer\SecondHandDtoTransformer;
use App\Entity\SecondHand;
use App\Form\SecondHandType;
use App\Repository\ParameterRepository;
use App\Repository\SecondHandRepository;
use App\Service\MessageService;
use App\Service\PaginatorService;
use App\Service\UploadService;
use App\UseCase\SecondHand\EditSecondHand;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('admin/occasion', name: 'admin_second_hand_')]
class SecondHandController extends AbstractController
{
    public function __construct(
        private SecondHandDtoTransformer $secondHandDtoTransformer,
        private SecondHandRepository $secondHandRepository,
        private ParameterRepository $parameterRepository,
    ) {
    }

    #[Route('/list/{valid}', name: 'list', defaults: ['valid' => SecondHandDtoTransformer::UN_VALIDED], methods: ['GET', 'POST'])]
    #[IsGranted('SECOND_HAND_LIST')]
    public function list(
        PaginatorService $paginator,
        PaginatorDtoTransformer $paginatorDtoTransformer,
        MessageService $messageService,
        Request $request,
        bool $valid,
    ): Response {
        $query = $this->secondHandRepository->findSecondHandQuery($valid);
        $secondHands = $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);
        return $this->render('second_hand/admin/list.html.twig', [
            'second_hands' => $this->secondHandDtoTransformer->fromEntities($secondHands),
            'paginator' => $paginatorDtoTransformer->fromEntities($secondHands, ['type' => (int)$valid]),
            'valid' => $valid,
            'settings' => [
                'parameters' => $this->parameterRepository->findByParameterGroupName('SECOND_HAND'),
                'routes' => [
                    ['name' => 'admin_category_list', 'label' => 'Catégories d\'occasions'],
                ],
                'messages' => $messageService->getMessagesBySectionName('SECOND_HAND'),
            ]
        ]);
    }

    #[Route('/detail/{secondHand}', name: 'show', methods: ['GET'])]
    #[IsGranted('SECOND_HAND_VIEW', 'secondHand')]
    public function show(SecondHand $secondHand): Response
    {
        return $this->render('second_hand/admin/show.html.twig', [
            'second_hand' => $this->secondHandDtoTransformer->fromEntity($secondHand),
        ]);
    }



    #[Route('/edit/{secondHand}', name: 'edit', defaults: ['secondHand' => null], methods: ['GET', 'POST'])]
    #[IsGranted('SECOND_HAND_EDIT', 'secondHand')]
    public function edit(
        Request $request,
        ?SecondHand $secondHand,
        EditSecondHand $editSecondHand,
    ): Response {
        $form = $this->createForm(SecondHandType::class, $secondHand, [
            'action' => $this->generateUrl('admin_second_hand_edit', [
                'secondHand' => $secondHand?->getId(),
            ]),
        ]);
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $secondHand = $form->getData();
            
            $editSecondHand->saveImages($secondHand, $request);
            $this->secondHandRepository->save($secondHand, true);
            return $this->redirectToRoute('admin_second_hand_list');
        }

        return $this->render('second_hand/admin/edit.html.twig', [
            'second_hand' => $this->secondHandDtoTransformer->fromEntity($secondHand),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/delete/{secondHand}', name: 'delete', methods: ['GET', 'POST'])]
    #[IsGranted('SECOND_HAND_EDIT', 'secondHand')]
    public function delete(
        Request $request,
        SecondHand $secondHand
    ): Response {
        $form = $this->createForm(FormType::class, null, [
            'action' => $this->generateUrl('admin_second_hand_delete', [
                'secondHand' => $secondHand->getId(),
            ]),
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $this->secondHandRepository->remove($secondHand, true);

            return $this->redirectToRoute('admin_second_hand_list', [
                'valid' => null !== $secondHand->getValidedAt(),
            ]);
        }

        return $this->render('second_hand/delete.modal.html.twig', [
            'second_hand' => $this->secondHandDtoTransformer->fromEntity($secondHand),
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/valider/{secondHand}', name: 'validate', methods: ['GET'])]
    #[IsGranted('SECOND_HAND_EDIT', 'secondHand')]
    public function validate(
        SecondHand $secondHand
    ): Response {
        $secondHand->setValidedAt(new DateTimeImmutable());
        $this->secondHandRepository->save($secondHand, true);

        return $this->redirectToRoute('admin_second_hand_list');
    }
}
