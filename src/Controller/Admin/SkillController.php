<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Service\ExportService;
use App\Repository\SkillRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Dto\DtoTransformer\SkillDtoTransformer;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route(path: '/admin/skill', name: 'admin_skill_')]
class SkillController extends AbstractController
{
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
        SkillRepository $skillRepository,
        SkillDtoTransformer $skillDtoTransformer,
    ): Response {

        $skills = $skillRepository->findAll();
        $content = $exportService->exportSkills($skillDtoTransformer->fromEntities($skills));

        $response = new Response($content);
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            'export_competences.csv'
        );
        
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
