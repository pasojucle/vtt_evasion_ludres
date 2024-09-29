<?php

declare(strict_types=1);

namespace App\Controller\API;

use App\Entity\Skill;
use App\Form\Admin\SkillType;
use App\Repository\SkillRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Dto\DtoTransformer\SkillDtoTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route(path: '/api/Skill', name: 'api_skill_')]
class SkillController extends AbstractController
{
    public function __construct(
        private readonly SkillDtoTransformer $transformer,
        private readonly EntityManagerInterface $entityManager,
    )
    {
        
    }
    #[Route(path: '/list', name: 'list', methods: ['GET'], options: ['expose' => true])]
    public function list(SkillRepository $skillRepository): JsonResponse
    {
        return new JsonResponse([
            'list' => $this->transformer->fromEntities($skillRepository->findAllOrdered()),
        ]);
    }



    #[Route(path: '/add', name: 'add', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function add(): JsonResponse
    {

        return new JsonResponse([
            
        ]);
    }

    #[Route(path: '/edit/{id}', name: 'edit', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function edit(Request $request, Skill $skill): JsonResponse|Response
    {
        $form = $this->createForm(SkillType::class, $skill, [
            'action' => $request->getRequestUri(),
        ]);
        $form->handleRequest($request);
        if ($request->getMethod('post') && $form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            return new JsonResponse([
                'success' => true, 
                'data' => [
                    'entity' => 'skill',
                    'value' => $this->transformer->fromEntity($skill),
                    'sort' => 'nameASC',
                ],
            ]);
        }
        
        return new JsonResponse([
            'form' => [
                'action' => $form->getConfig()->getAction(),
                'elements' => $this->renderView('skill/admin/edit.modal.html.twig',[
                    'form' => $form->createView()
                ]),
                'submit' => 'Enregistrer',
            ],
            'title' => 'Mofifier la compétence',
        ]);
    }

    #[Route(path: '/delete', name: 'delete', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function delete(): JsonResponse
    {

        return new JsonResponse([
            
        ]);
    }
}
