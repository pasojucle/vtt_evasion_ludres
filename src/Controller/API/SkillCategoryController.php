<?php

declare(strict_types=1);

namespace App\Controller\API;

use App\Service\ApiService;
use App\Entity\SkillCategory;
use App\Form\Admin\SkillCategoryType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\SkillCategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Dto\DtoTransformer\SkillCategoryDtoTransformer;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route(path: '/api/skillCategory', name: 'api_skill_category_')]
class SkillCategoryController extends AbstractController
{
    public function __construct(
        private readonly SkillCategoryDtoTransformer $transformer,
        private readonly EntityManagerInterface $entityManager,
        private readonly ApiService $api,
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
        $form = $this->api->createForm($request, SkillCategoryType::class, $category);
        $form->handleRequest($request);
        if ($request->getMethod('post') && $form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($category);
            $this->entityManager->flush();
            return $this->api->responseForm($category, $this->transformer);
        }
        
        return $this->api->renderModal($form, 'Ajouter une catégorie', 'Enregistrer');
    }

    #[Route(path: '/edit/{id}', name: 'edit', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function edit(Request $request, SkillCategory $category): JsonResponse
    {
        $form = $this->api->createForm($request, SkillCategoryType::class, $category);
        $form->handleRequest($request);
        if ($request->getMethod('post') && $form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            return $this->api->responseForm($category, $this->transformer);
        }
        
        return $this->api->renderModal($form,'Mofifier la catégorie', 'Enregistrer');
    }

    #[Route(path: '/delete/{id}', name: 'delete', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function delete(Request $request, SkillCategory $category): JsonResponse
    {
        $form = $this->api->createForm($request, FormType::class, $category);
        $form->handleRequest($request);
        if ($request->getMethod('post') && $form->isSubmitted() && $form->isValid()) {
            $response = $this->api->responseForm($category, $this->transformer, 'nameASC', true);
            $this->entityManager->remove($category);
            $this->entityManager->flush();
            return $response;
        }
        
        $message = sprintf('Etes vous certain de supprimer la catégorie %s?', $category->getName());
        return $this->api->renderModal($form,'Supprimer la catégorie', 'Supprimer', $message);
    }
}
