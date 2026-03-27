<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\SkillDtoTransformer;
use App\Entity\Skill;
use App\Repository\SkillRepository;
use App\Service\ExportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/admin/skill', name: 'admin_skill_')]
class SkillController extends AbstractController
{
    public function __construct(
        private SkillRepository $skillRepository
    )
    {
    }

    #[Route(path: '/list', name: 'list', methods: ['GET'])]
    #[IsGranted('SKILL_LIST')]
    public function list(): Response
    {
        return $this->render('skill/admin/list.html.twig', [
            'settings' => [
                'parameters' => [],
                'routes' => [
                    ['name' => 'admin_skill_category_list', 'label' => 'Catégories'],
                ],
            ],
        ]);
    }

    #[Route('/export', name: 'export', methods: ['GET'])]
    #[IsGranted('SKILL_LIST')]
    public function adminOrderHeadersExport(
        ExportService $exportService,
        SkillDtoTransformer $skillDtoTransformer,
    ): Response {
        $skills = $this->skillRepository->findAll();
        $content = $exportService->exportSkills($skillDtoTransformer->fromEntities($skills));

        $response = new Response($content);
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            'export_competences.csv'
        );
        
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    #[Route('/autocomplete', name: 'autocomplete', methods: ['GET', 'POST'])]
    #[IsGranted('IS_MEMBER')]
    public function adminSkillAutocomplete(
        Request $request
    ): JsonResponse {
        $cluster = $request->query->get('cluster');
        $category = $request->query->get('category');
        $level = $request->query->get('level');

        $results = [];
        $skills = ($category || $level)
            ? $this->skillRepository->findFiltered($category, $level)
            : $this->skillRepository->findAllOrdered();

        dump($level, $skills);
        /** @var Skill $kill */
        foreach($skills as $skill) {
            $results[] = [
                'value' => $skill->getId(),
                'text' => $skill->getContent(),
            ];
        }

        return new JsonResponse(['results' => $results]);
    }
}
