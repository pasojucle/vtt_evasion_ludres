<?php

declare(strict_types=1);

namespace App\Controller\API;

use App\Dto\DtoTransformer\SkillDtoTransformer;
use App\Entity\Skill;
use App\Form\Admin\SkillType;
use App\Repository\SkillRepository;
use App\Service\ApiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/api/skill', name: 'api_skill_')]
class SkillController extends AbstractController
{
    public function __construct(
        private readonly SkillDtoTransformer $transformer,
        private readonly EntityManagerInterface $entityManager,
        private readonly ApiService $api,
    ) {
    }

    #[Route(path: '/list', name: 'list', methods: ['GET'], options: ['expose' => true])]
    #[IsGranted('SKILL_LIST')]
    public function list(SkillRepository $skillRepository): JsonResponse
    {
        return new JsonResponse([
            'list' => $this->transformer->fromEntities($skillRepository->findAllOrdered()),
        ]);
    }

}
