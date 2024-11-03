<?php

declare(strict_types=1);

namespace App\Controller\API;

use App\Dto\DtoTransformer\SkillDtoTransformer;
use App\Entity\Cluster;
use App\Entity\Skill;
use App\Entity\UserSkill;
use App\Form\Admin\SkillAddType;
use App\Form\Admin\UserSkillCollectionType;
use App\Form\Admin\UserSkillType;
use App\Service\ApiService;
use App\UseCase\Skill\GetUserSkillCluster;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/cluster_skill', name: 'api_cluster_skill_')]
class ClusterSkillController extends AbstractController
{
    public function __construct(
        private readonly SkillDtoTransformer $transformer,
        private readonly EntityManagerInterface $entityManager,
        private readonly GetUserSkillCluster $getUserSkillCluster,
        private readonly ApiService $api,
    ) {
    }

    #[Route(path: '/list/{cluster}', name: 'list', methods: ['GET'], options: ['expose' => true])]
    public function list(Cluster $cluster): JsonResponse
    {
        return new JsonResponse([
            'list' => $this->transformer->fromEntities($cluster->getSkills()),
        ]);
    }

    #[Route(path: '/eval/{cluster}/{skill}', name: 'eval', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function eval(Request $request, Cluster $cluster, Skill $skill): JsonResponse
    {
        $form = $this->createForm(UserSkillCollectionType::class, $this->getUserSkillCluster->execute($cluster, $skill), [
            'action' => $request->getUri(),
            'text_type' => UserSkillType::BY_USERS,
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('post') && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            /** @var UserSkill $userSkill */
            foreach ($data['userSkills'] as $userSkill) {
                $userSkill->setEvaluateAt(new DateTimeImmutable());
            }
            $this->entityManager->flush();

            return $this->api->responseForm($skill, $this->transformer, 'idASC', false, 'cluster_skill');
        }

        return $this->api->renderModal($form, 'Evaluer', 'Enregistrer', 'primary', $skill->getContent());
    }

    #[Route(path: '/add/{cluster}', name: 'add', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function add(Request $request, Cluster $cluster): JsonResponse
    {
        $form = $this->api->createForm($request, SkillAddType::class, null, [
            'exclude' => ['entity' => 'cluster_skill', 'field' => 'id'],
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('post') && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $skill = $this->entityManager->getRepository(Skill::class)->find($data['skill']);
            $cluster->addSkill($skill);
            $this->entityManager->flush();

            return $this->api->responseForm($skill, $this->transformer, 'idASC', false, 'cluster_skill');
        }

        return $this->api->renderModal($form, 'Mofifier la compétence', 'Enregistrer');
    }

    #[Route(path: '/delete/{cluster}/{skill}', name: 'delete', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function delete(Request $request, Cluster $cluster, Skill $skill): JsonResponse
    {
        $form = $this->api->createForm($request, FormType::class, $skill);
        $form->handleRequest($request);
        if ($request->isMethod('post') && $form->isSubmitted() && $form->isValid()) {
            $response = $this->api->responseForm($skill, $this->transformer, 'idASC', true, 'cluster_skill');
            $cluster->removeSkill($skill);
            $this->entityManager->flush();
            return $response;
        }
        
        $message = sprintf('<p>Etes vous certain de supprimer la compétence ? %s</p>', $skill->getContent());
        return $this->api->renderModal($form, 'Supprimer la compétence', 'Supprimer', $message);
    }
}
