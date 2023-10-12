<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\PaginatorDtoTransformer;
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
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/param/niveau', name: 'admin_level')]
class LevelController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LevelRepository $levelRepository,
        private OrderByService $orderByService
    ) {
    }

    #[Route('x/{type}', name: 's', methods: ['GET'], defaults:['type' => 1])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminList(
        PaginatorService $paginator,
        PaginatorDtoTransformer $paginatorDtoTransformer,
        Request $request,
        int $type
    ): Response {
        $query = $this->levelRepository->findLevelQuery($type);
        $levels = $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);
        return $this->render('level/admin/list.html.twig', [
            'levels' => $levels,
            'paginator' => $paginatorDtoTransformer->fromEntities($levels, ['type' => (int) $type]),
            'current_type' => $type,
        ]);
    }

    #[Route('/{level}', name: '_edit', methods: ['GET', 'POST'], defaults:['level' => null])]
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

            return $this->redirectToRoute('admin_levels', [
                'type' => $level->getType(),
            ]);
        }

        return $this->render('level/admin/edit.html.twig', [
            'level' => $level,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/supprimer/{level}', name: '_delete', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
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

    #[Route('/ordonner/{level}', name: '_order', methods: ['POST'], options:['expose' => true])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminLevelOrder(
        Request $request,
        Level $level
    ): Response {
        $type = $level->getType();
        $newOrder = (int) $request->request->get('newOrder');
        $levels = $this->levelRepository->findByType($type);

        $this->orderByService->setNewOrders($level, $levels, $newOrder);

        return new Response();
    }
}
