<?php

declare(strict_types=1);

namespace App\Controller\API;

use App\Entity\Skill;
use App\Entity\Cluster;
use App\Entity\UserSkill;
use App\Service\ApiService;
use App\Form\Admin\SkillType;
use App\Form\Admin\ClusterSkillType;
use App\Repository\UserSkillRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Dto\DtoTransformer\SkillDtoTransformer;
use App\Form\Admin\UserSkillCollectionType;
use App\Form\Admin\UserSkillType;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route(path: '/api/cluster_skill', name: 'api_cluster_skill_')]
class ClusterSkillController extends AbstractController
{
    public function __construct(
        private readonly SkillDtoTransformer $transformer,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserSkillRepository $userSkillRepository,
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

    #[Route(path: '/eval/{cluster}/{skill}', name: 'eval', methods: ['GET'], options: ['expose' => true])]
    public function eval(Request $request, Cluster $cluster, Skill $skill): JsonResponse
    {
        dump($cluster->getSessions());

        $userSkills = [];
        /** @var UserSkill $userSkill */
        foreach($this->userSkillRepository->findByClusterAndSkill($cluster, $skill) as $userSkill) {
            $userSkills[$userSkill->getUser()->getId()] = $userSkill;
        }


        $clusterUserSkill = [];
        /** @var Session $session */
        foreach($cluster->getSessions() as $session) {
            if ($session->isPresent()) {
                $user = $session->getUser();

                $clusterUserSkill[] = (array_key_exists($user->getId(), $userSkills))
                    ? $userSkills[$user->getId()]
                    : (new UserSkill())->setUser($user)->setSkill($skill); 
            }
        }

        $form = $this->createForm(UserSkillCollectionType::class, ['collection' => new ArrayCollection($clusterUserSkill)]);

        dump($clusterUserSkill);
        dump($form);
        
        $form->handleRequest($request);
        if ($request->getMethod('post') && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $skill = $this->entityManager->getRepository(Skill::class)->find($data['skill']);
            $cluster->addSkill($skill);
            $this->entityManager->flush();

            return $this->api->responseForm($skill, $this->transformer, 'idASC', false, 'cluster_skill');
        }

        return $this->api->renderModal($form, 'Evaluer', 'Enregistrer');
    }

    #[Route(path: '/add/{cluster}', name: 'add', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function add(Request $request, Cluster $cluster): JsonResponse
    {
        $form = $this->api->createForm($request, ClusterSkillType::class, null);

        $form->handleRequest($request);
        if ($request->getMethod('post') && $form->isSubmitted() && $form->isValid()) {
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
        if ($request->getMethod('post') && $form->isSubmitted() && $form->isValid()) {
            $response = $this->api->responseForm($skill, $this->transformer, 'idASC', true, 'cluster_skill');
            $cluster->removeSkill($skill);
            $this->entityManager->flush();
            return $response;
        }
        
        $message = sprintf('<p>Etes vous certain de supprimer la compétence ? %s</p>', $skill->getContent());
        return $this->api->renderModal($form, 'Supprimer la compétence', 'Supprimer', $message);
    }
}
