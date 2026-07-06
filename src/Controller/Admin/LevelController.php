<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\Filter\LevelFilter;
use App\Dto\Payload\LevelOrderDto;
use App\Entity\Level;
use App\Form\Admin\LevelType;
use App\Repository\LevelRepository;
use App\State\Level\Processor\LevelDeleteProcessor;
use App\State\Level\Processor\LevelOrderProcessor;
use App\State\Level\Processor\LevelRestoreProcessor;
use App\State\Level\Provider\LevelDeleteProvider;
use App\State\Level\Provider\LevelListProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/param/niveau', name: 'admin_level_')]
class LevelController extends AbstractCrudController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LevelRepository $levelRepository,
    ) {
    }

    #[Route('x', name: 'list', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminList(
        Request $request,
        LevelListProvider $provider,
    ): Response {
        return $this->handleListAction(
            LevelFilter::class,
            $provider,
            $request
        );
    }

    #[Route('/{level}', name: 'edit', methods: ['GET', 'POST'], defaults:['level' => null])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminLevelEdit(
        Request $request,
        ?Level $level
    ): Response {
        $form = $this->createForm(LevelType::class, $level);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $level = $form->getData();

            if (null === $level->getOrderBy() && null !== $level->getType()) {
                $order = $this->levelRepository->findNexOrderByType($level->getType());
                $level->setOrderBy($order);
            }
            $this->entityManager->persist($level);
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_level_list', [
                'type' => $level->getType(),
            ]);
        }

        return $this->render('level/admin/edit.html.twig', [
            'level' => $level,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/supprimer/{level}', name: 'delete', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminLevelDelete(
        Request $request,
        LevelDeleteProcessor $processor,
        LevelDeleteProvider $provider,
        Level $level
    ): Response {
        return $this->handleFormComponentAction(
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
        LevelRestoreProcessor $processor,
        Level $level
    ): Response {
        return $this->handleProcessAction(
            $request,
            $level,
            $processor
        );
    }

    #[Route('/ordonner/{level}', name: 'order', methods: ['POST'], options:['expose' => true])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminLevelOrder(
        Request $request,
        LevelOrderProcessor $processor,
        Level $level
    ): Response {
        return $this->handleJsonProcessAction(
            $request,
            new LevelOrderDto($level, (int) $request->request->get('newOrder', 0)),
            $processor
        );
    }
}
