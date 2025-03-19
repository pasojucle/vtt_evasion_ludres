<?php

declare(strict_types=1);

namespace App\Controller\API;


use App\Repository\UserRepository;
use Symfony\Component\Routing\Attribute\Route;
use App\Dto\DtoTransformer\ApiUserDtoTransformer;
use App\Service\LevelService;
use App\Service\SeasonService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route(path: '/api/user', name: 'api_user_')]
class UserController extends AbstractController 
{
    public function __construct(
        private readonly ApiUserDtoTransformer $transformer,
        private readonly UserRepository $userRepository,
        private readonly SeasonService $seasonService,
        private readonly LevelService $levelService,
    )
    {
        
    }

    #[Route(path: '/list', name: 'list', methods: ['GET'], options: ['expose' => true])]
    public function list(): JsonResponse
    {
        $users = $this->userRepository->findAllAsc();

        return new JsonResponse([
            'list' => $this->transformer->listFromEntities($users),
            'seasons' => $this->seasonService->getChoicesFilter(), 
            'levels' => $this->levelService->getChoicesFilter()
        ]);
    }
}