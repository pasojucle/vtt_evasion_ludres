<?php

declare(strict_types=1);

namespace App\Controller\API;

use App\Dto\DtoTransformer\ApiUserDtoTransformer;
use App\Repository\UserRepository;
use App\Service\LevelService;
use App\Service\PermissionService;
use App\Service\SeasonService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/user', name: 'api_user_')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly ApiUserDtoTransformer $transformer,
        private readonly UserRepository $userRepository,
        private readonly SeasonService $seasonService,
        private readonly LevelService $levelService,
        private readonly PermissionService $permissionService,
    ) {
    }

    #[Route(path: '/list', name: 'list', methods: ['GET'], options: ['expose' => true])]
    public function list(): JsonResponse
    {
        
        return new JsonResponse([
            'list' => $this->transformer->listAll(),
            'seasons' => $this->seasonService->getChoicesFilter(),
            'levels' => $this->levelService->getChoicesFilter(),
            'permissions' => $this->permissionService->getChoicesFilter(),
        ]);
    }
}
