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
    public function list(SkillRepository $skillRepository): JsonResponse
    {
        return new JsonResponse([
            'list' => $this->transformer->fromEntities($skillRepository->findAllOrdered()),
        ]);
    }

    #[Route(path: '/add', name: 'add', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function add(Request $request): JsonResponse
    {
        $skill = new Skill();
        $form = $this->api->createForm($request, SkillType::class, $skill);
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($skill);
            $this->entityManager->flush();
            return $this->api->responseForm($skill, $this->transformer, 'idASC');
        }
        
        return $this->api->renderModal($form, 'Ajouter la compétence', 'Enregistrer');
    }

    #[Route(path: '/edit/{id}', name: 'edit', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function edit(Request $request, Skill $skill): JsonResponse|Response
    {
        $form = $this->api->createForm($request, SkillType::class, $skill);
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            return $this->api->responseForm($skill, $this->transformer, 'idASC');
        }
        
        return $this->api->renderModal($form, 'Mofifier la compétence', 'Enregistrer');
    }

    #[Route(path: '/delete/{id}', name: 'delete', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function delete(Request $request, Skill $skill): JsonResponse
    {
        $form = $this->api->createForm($request, FormType::class, $skill);
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $response = $this->api->responseForm($skill, $this->transformer, 'idASC', true);
            $this->entityManager->remove($skill);
            $this->entityManager->flush();
            return $response;
        }
        
        $message = sprintf('<p>Etes vous certain de supprimer la compétence ? %s</p>', $skill->getContent());
        return $this->api->renderModal($form, 'Supprimer la compétence', 'Supprimer', 'danger', $message);
    }
}
