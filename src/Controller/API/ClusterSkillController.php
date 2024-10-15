<?php

declare(strict_types=1);

namespace App\Controller\API;

use App\Entity\Skill;
use App\Entity\Cluster;
use App\Service\ApiService;
use App\Form\Admin\SkillType;
use App\Repository\SkillRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Dto\DtoTransformer\SkillDtoTransformer;
use App\Form\Admin\SkillFilterType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route(path: '/api/cluster_skill', name: 'api_cluster_skill_')]
class ClusterSkillController extends AbstractController
{
    public function __construct(
        private readonly SkillDtoTransformer $transformer,
        private readonly EntityManagerInterface $entityManager,
        private readonly ApiService $api,
    )
    {
        
    }

    #[Route(path: '/list/{cluster}', name: 'list', methods: ['GET'], options: ['expose' => true])]
    public function list(Cluster $cluster): JsonResponse
    {
        dump($cluster->getSkills()->count());
        return new JsonResponse([
            'list' => $this->transformer->fromEntities($cluster->getSkills()),
        ]);
    }

    #[Route(path: '/add/{cluster}', name: 'add', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function add(Request $request, Cluster $cluster): JsonResponse
    {
        $skill = new Skill();
        $form = $this->api->createForm($request, FormType::class, null);
        $form->handleRequest($request);
        if ($request->getMethod('post') && $form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($skill);
            $this->entityManager->flush();
            return $this->api->responseForm($skill, $this->transformer, 'idASC');
        }
        
        $components = [
            [
                'name' => 'SkillFilter',
                'params' => [],
            ]
        ];
        return $this->api->renderModal($form,'Ajouter une compétence', 'Ajouter', null, $components);
    }

    #[Route(path: '/edit/{cluster}/{skill}', name: 'edit', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function edit(Request $request, Skill $skill): JsonResponse|Response
    {
        $form = $this->api->createForm($request, SkillType::class, $skill);
        $form->handleRequest($request);
        if ($request->getMethod('post') && $form->isSubmitted() && $form->isValid()) {
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
        if ($request->getMethod('post') && $form->isSubmitted() && $form->isValid()) {
            $response = $this->api->responseForm($skill, $this->transformer, 'idASC', true);
            $this->entityManager->remove($skill);
            $this->entityManager->flush();
            return $response;
        }
        
        $message = sprintf('<p>Etes vous certain de supprimer la compétence ? %s</p>', $skill->getContent());
        return $this->api->renderModal($form, 'Supprimer la compétence', 'Supprimer', $message);
    }
}
