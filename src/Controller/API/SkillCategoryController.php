<?php

declare(strict_types=1);

namespace App\Controller\API;

use App\Entity\SkillCategory;
use App\Form\Admin\SkillCategoryType;
use App\Repository\SkillCategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Dto\DtoTransformer\SkillCategoryDtoTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route(path: '/api/SkillCategory', name: 'api_skill_category_')]
class SkillCategoryController extends AbstractController
{
    public function __construct(
        private readonly SkillCategoryDtoTransformer $transformer,
        private readonly EntityManagerInterface $entityManager,
    )
    {
        
    }
    #[Route(path: '/list', name: 'list', methods: ['GET'], options: ['expose' => true])]
    public function list(SkillCategoryRepository $skillCategoryRepository): JsonResponse
    {
        return new JsonResponse([
            'list' => $this->transformer->fromEntities($skillCategoryRepository->findAllOrdered()),
        ]);
    }



    #[Route(path: '/add', name: 'add', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function add(Request $request): JsonResponse
    {
        $category = new SkillCategory();
        $form = $this->createForm(SkillCategoryType::class, $category, [
            'action' => $request->getRequestUri(),
        ]);
        $form->handleRequest($request);
        if ($request->getMethod('post') && $form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($category);
            $this->entityManager->flush();
            return new JsonResponse([
                'success' => true, 
                'data' => [
                    'entity' => 'skill_category',
                    'value' => $this->transformer->fromEntity($category),
                    'sort' => 'nameASC',
                ],
            ]);
        }
        
        return new JsonResponse([
            'form' => [
                'action' => $form->getConfig()->getAction(),
                'elements' => $this->renderView('skill_category/admin/edit.modal.html.twig',[
                    'form' => $form->createView()
                ]),
                'submit' => 'Enregistrer',
            ],
            'title' => 'Ajouter une catégorie',
        ]);
    }

    #[Route(path: '/edit/{id}', name: 'edit', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function edit(Request $request, SkillCategory $category): JsonResponse
    {
        $form = $this->createForm(SkillCategoryType::class, $category, [
            'action' => $request->getRequestUri(),
        ]);
        $form->handleRequest($request);
        if ($request->getMethod('post') && $form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            return new JsonResponse([
                'success' => true, 
                'data' => [
                    'entity' => 'skill_category',
                    'value' => $this->transformer->fromEntity($category),
                    'sort' => 'nameASC',
                ],
            ]);
        }
        
        return new JsonResponse([
            'form' => [
                'action' => $form->getConfig()->getAction(),
                'elements' => $this->renderView('skill_category/admin/edit.modal.html.twig',[
                    'form' => $form->createView()
                ]),
                'submit' => 'Enregistrer',
            ],
            'title' => 'Mofifier la catégorie',
        ]);
    }

    #[Route(path: '/delete', name: 'delete', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function delete(): JsonResponse
    {

        return new JsonResponse([
            
        ]);
    }
}
