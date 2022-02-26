<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Level;
use App\Form\Admin\LevelType;
use App\Repository\LevelRepository;
use App\Service\OrderByService;
use App\Service\PaginatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LevelController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LevelRepository $levelRepository,
        private OrderByService $orderByService
    ) {
    }

    #[Route('/admin/niveaux/{type}', name: 'admin_levels', methods: ['GET'], defaults:['type' => 1])]
    public function adminList(
        PaginatorService $paginator,
        Request $request,
        int $type
    ): Response {
        $query = $this->levelRepository->findLevelQuery($type);
        $levels = $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);

        return $this->render('level/admin/list.html.twig', [
            'levels' => $levels,
            'lastPage' => $paginator->lastPage($levels),
            'current_type' => $type,
            'current_filters' => [
                'type' => (int) $type,
            ],
        ]);
    }

    #[Route('/admin/niveau/{level}', name: 'admin_level_edit', methods: ['GET', 'POST'], defaults:['level' => null])]
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

            return $this->redirectToRoute('admin_levels', [
                'type' => $level->getType(),
            ]);
        }

        return $this->render('level/admin/edit.html.twig', [
            'level' => $level,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/supprimer/niveau/{level}', name: 'admin_level_delete', methods: ['GET', 'POST'])]
    public function adminLevelDelete(
        Request $request,
        Level $level
    ): Response {
        $form = $this->createForm(FormType::class, null, [
            'action' => $this->generateUrl(
                'admin_level_delete',
                [
                    'level' => $level->getId(),
                ]
            ),
        ]);
        $type = $level->getType();

        $form->handleRequest($request);
        if ($request->isMethod('post') && $form->isSubmitted() && $form->isValid()) {
            $level->setIsDeleted(true);
            $this->entityManager->persist($level);
            $this->entityManager->flush();

            $levels = $this->levelRepository->findByType($type);
            $this->orderByService->ResetOrders($levels);

            return $this->redirectToRoute('admin_levels', [
                'type' => $type,
            ]);
        }

        return $this->render('level/admin/delete.modal.html.twig', [
            'level' => $level,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/ordonner/niveau/{level}', name: 'admin_level_order', methods: ['POST'], options:['expose' => true])]
    public function adminLevelOrder(
        Request $request,
        Level $level
    ): Response {
        $type = $level->getType();
        $newOrder = $request->request->get('newOrder');
        $levels = $this->levelRepository->findByType($type);

        $this->orderByService->setNewOrders($level, $levels, $newOrder);

        return new Response();
    }
}
