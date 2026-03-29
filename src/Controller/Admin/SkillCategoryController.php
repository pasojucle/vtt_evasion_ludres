<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\SkillCategoryDtoTransformer;
use App\Entity\SkillCategory;
use App\Form\Admin\SkillCategoryType;
use App\Repository\SkillCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Turbo\TurboBundle;

#[Route(path: '/admin/skill/category', name: 'admin_skill_category_')]
class SkillCategoryController extends AbstractController
{
    public function __construct(
        private SkillCategoryRepository $skillCategoryRepository,
        private readonly SkillCategoryDtoTransformer $transformer,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route(path: '/list', name: 'list', methods: ['GET'])]
    public function list(): Response
    {
        return $this->render('skill_category/admin/list.html.twig', [
            'settings' => [
                'parameters' => [],
                'routes' => [
                    ['name' => 'admin_skill_category_list', 'label' => 'Catégories'],
                ],
            ],
            'skillCategories' => $this->transformer->fromEntities($this->skillCategoryRepository->findAllOrdered()),
        ]);
    }


    #[Route(path: '/add', name: 'add', methods: ['GET', 'POST'], options: ['expose' => true])]
    #[IsGranted('SKILL_ADD')]
    public function add(Request $request): Response
    {
        $queryParams = $request->query->all();
        $skillCategory = new SkillCategory();
        $response = new Response("OK", Response::HTTP_OK);
        $form = $this->createForm(SkillCategoryType::class, $skillCategory, [
            'action' => $request->getUri(),
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted()) {
            if ($form->isValid()) {
                $skillCategory = $form->getData();
                $this->entityManager->persist($skillCategory);
                $this->entityManager->flush();

                return $this->redirectToRoute('admin_skill_category_list', $queryParams);
            }
            $response = new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->render('skill_category/admin/skill_category_edit.modal.html.twig', [
            'form' => $form->createView(),
            'title' => 'Ajouter une catégorie',
            'btn' => ['label' => 'Ajouter', 'icon' => 'lucide:plus'],
        ], $response);
    }

    #[Route(path: '/edit/{skillCategory}', name: 'edit', methods: ['GET', 'POST'])]
    #[IsGranted('SKILL_EDIT', 'skillCategory')]
    public function edit(
        Request $request,
        SkillCategory $skillCategory
    ): Response {
        $queryParams = $request->query->all();
        $response = new Response("OK", Response::HTTP_OK);
        $form = $this->createForm(SkillCategoryType::class, $skillCategory, [
            'action' => $request->getUri(),
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted()) {
            if ($form->isValid()) {
                $skillCategory = $form->getData();
                $this->entityManager->flush();

                return $this->redirectToRoute('admin_skill_category_list', $queryParams);
            }
            $response = new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->render('skill_category/admin/skill_category_edit.modal.html.twig', [
            'form' => $form->createView(),
            'title' => 'Modifier une catégorie',
            'btn' => ['label' => 'Modifier', 'icon' => 'lucide:pen'],
        ], $response);
    }

    #[Route(path: '/delete/{skillCategory}', name: 'delete', methods: ['GET', 'POST'], options: ['expose' => true])]
    #[IsGranted('SKILL_EDIT', 'skillCategory')]
    public function delete(
        Request $request,
        SkillCategory $skillCategory
    ): Response {
        $queryParams = $request->query->all();
        $response = new Response("OK", Response::HTTP_OK);
        $form = $this->createForm(FormType::class, null, [
            'action' => $request->getUri(),
            'attr' => ['data-action' => 'turbo:submit-end->modal#handleFormSubmit']
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted()) {
            if ($form->isValid()) {
                $skillCategoryId = $skillCategory->getId();
                $this->entityManager->remove($skillCategory);
                $this->entityManager->flush();
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                if ($request->getPreferredFormat() === TurboBundle::STREAM_FORMAT) {
                    return $this->render('cluster/admin/skill_deleted.stream.html.twig', [
                        'skillCategoryId' => $skillCategoryId,
                    ]);
                }

                return $this->redirectToRoute('admin_skillcategory_list', $queryParams);
            }
            $response = new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        
        return $this->render('component/destructive.modal.html.twig', [
            'title' => 'Supprimer une compétence',
            'content' => sprintf('Etes vous certain de supprimer la compétence %s ?', $skillCategory->getName()),
            'btn_label' => 'Supprimer',
            'form' => $form->createView(),
        ], $response);
    }
}
