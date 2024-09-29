<?php

declare(strict_types=1);

namespace App\Controller\API;

use App\Repository\SkillCategoryRepository;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Dto\DtoTransformer\SkillCategoryDtoTransformer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route(path: '/api/SkillCategory', name: 'api_skill_category_')]
class SkillCategoryController extends AbstractController
{
    public function __construct(
        private readonly SkillCategoryDtoTransformer $transformer,
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
    public function add(): JsonResponse
    {

        return new JsonResponse([
            
        ]);
    }

    #[Route(path: '/edit', name: 'edit', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function edit(): JsonResponse
    {

        return new JsonResponse([
            
        ]);
    }

    #[Route(path: '/delete', name: 'delete', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function delete(): JsonResponse
    {

        return new JsonResponse([
            
        ]);
    }
}
