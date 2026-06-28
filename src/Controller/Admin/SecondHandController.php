<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\SecondHandDtoTransformer;
use App\Dto\Filter\SecondHandFilter;
use App\Entity\SecondHand;
use App\Form\SecondHandType;
use App\Repository\SecondHandRepository;
use App\State\SecondHand\Processor\SecondHandDeleteProcessor;
use App\State\SecondHand\Provider\SecondHandDeleteProvider;
use App\State\SecondHand\Provider\SecondHandDetailProvider;
use App\State\SecondHand\Provider\SecondHandListProvider;
use App\UseCase\SecondHand\EditSecondHand;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('admin/occasion', name: 'admin_second_hand_')]
class SecondHandController extends AbstractCrudController
{
    public function __construct(
        private SecondHandDtoTransformer $secondHandDtoTransformer,
        private SecondHandRepository $secondHandRepository,
    ) {
    }

    #[Route('/list', name: 'list', methods: ['GET'])]
    #[IsGranted('SECOND_HAND_LIST')]
    public function list(
        SecondHandListProvider $provider,
        Request $request,
    ): Response {
        return $this->handleListAction(
            SecondHandFilter::class,
            $provider,
            $request
        );
    }

    #[Route('/detail/{secondHand}', name: 'show', methods: ['GET'])]
    #[IsGranted('SECOND_HAND_VIEW', 'secondHand')]
    public function show(
        Request $request,
        SecondHandDetailProvider $provider,
        SecondHand $secondHand
    ): Response
    {

        return $this->render('second_hand/admin/show.html.twig', [
            'second_hand' => $provider->getDetailView(
                $secondHand, 
                $request->attributes->get('_route'),
                $request->query->get('_redirect_to')
            ),
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
         SecondHandDeleteProcessor $processor,
         SecondHandDeleteProvider $provider,
         SecondHand $secondHand
     ): Response {
         return $this->handleFormComponentAction(
             $request,
             $secondHand,
             $provider,
             $processor,
         );
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
