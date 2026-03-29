<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\SkillDtoTransformer;
use App\Entity\Cluster;
use App\Entity\MemberSkill;
use App\Entity\Skill;
use App\Form\Admin\ClusterSkillAddType;
use App\Form\Admin\MemberSkillCollectionType;
use App\Form\Admin\MemberSkillType;
use App\UseCase\Skill\GetUserSkillCluster;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Turbo\TurboBundle;

class ClusterSkillController extends AbstractController
{
    public function __construct(
        private SkillDtoTransformer $skillDtoTransformer,
        private EntityManagerInterface $entityManager,
        private readonly GetUserSkillCluster $getUserSkillCluster,
    ) {
    }

    #[Route('/admin/groupe/evaluations/{cluster}', name: 'admin_cluster_skills', methods: ['GET', 'POST'])]
    #[IsGranted('BIKE_RIDE_LIST')]
    public function adminClusterEvaluations(
        Cluster $cluster,
    ): Response {
        return $this->render('cluster/admin/skill_list.html.twig', [
            'cluster' => $cluster,
            'clusterSkills' => $this->skillDtoTransformer->fromEntities($cluster->getSkills()),
            'canEdit' => $this->isGranted('BIKE_RIDE_EDIT', $cluster),
        ]);
    }

    #[Route('/admin/groupe/evaluation/add/{cluster}', name: 'admin_cluster_skill_add', methods: ['GET', 'POST'])]
    #[IsGranted('BIKE_RIDE_LIST')]
    public function adminClusterEvaluationAdd(
        Cluster $cluster,
        Request $request,
    ): Response {
        $response = new Response("OK", Response::HTTP_OK);
        $form = $this->createForm(ClusterSkillAddType::class, null, [
            'action' => $request->getUri(),
            'clusterId' => $cluster->getId(),
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted()) {
            if ($form->isValid()) {
                $skill = $form->get('skill')->getData();
                $cluster->addSkill($skill);
                $this->entityManager->flush();

                if ($request->getPreferredFormat() === TurboBundle::STREAM_FORMAT) {
                    $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
                    
                    return $this->render('cluster/admin/skill_added.stream.html.twig', [
                        'skill' => $this->skillDtoTransformer->fromEntity($skill),
                        'cluster' => $cluster,
                        'canEdit' => $this->isGranted('BIKE_RIDE_EDIT', $cluster),
                    ]);
                }

                return $this->redirectToRoute('admin_cluster_skills', ['cluster' => $cluster->getId()]);
            }
            $response = new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->render('cluster/admin/skill_add.modal.html.twig', [
            'form' => $form->createView(),
            'cluster' => $cluster,
        ], $response);
    }

    #[Route('/admin/groupe/evaluation/delete/{cluster}/{skill}', name: 'admin_cluster_skill_delete', methods: ['GET', 'POST'])]
    #[IsGranted('BIKE_RIDE_LIST')]
    public function adminClusterEvaluationdDelete(
        Cluster $cluster,
        Skill $skill,
        Request $request,
    ): Response {
        $response = new Response("OK", Response::HTTP_OK);
        $form = $this->createForm(FormType::class, null, [
            'action' => $request->getUri(),
            'attr' => ['data-action' => 'turbo:submit-end->modal#handleFormSubmit']
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted()) {
            if ($form->isValid()) {
                $skillId = $skill->getId();
                $cluster->removeSkill($skill);
                $this->entityManager->flush();
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                if ($request->getPreferredFormat() === TurboBundle::STREAM_FORMAT) {
                    return $this->render('cluster/admin/skill_deleted.stream.html.twig', [
                        'skillId' => $skillId,
                    ]);
                }

                return $this->redirectToRoute('admin_cluster_skills', ['cluster' => $cluster->getId()]);
            }
            $response = new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        
        return $this->render('component/destructive.modal.html.twig', [
            'title' => 'Supprimer une évaluation',
            'content' => sprintf('Etes vous certain de supprimer l\'évaluation %s ?', $skill->getContent()),
            'btn_label' => 'Supprimer',
            'form' => $form->createView(),
        ], $response);
    }

    #[Route('/admin/groupe/evaluation/assess/{cluster}/{skill}', name: 'admin_cluster_skill_assess', methods: ['GET', 'POST'])]
    #[IsGranted('BIKE_RIDE_LIST')]
    public function adminClusterEvaluationdAssess(
        Cluster $cluster,
        Skill $skill,
        Request $request,
    ): Response {
        $form = $this->createForm(MemberSkillCollectionType::class, $this->getUserSkillCluster->execute($cluster, $skill), [
            'action' => $request->getUri(),
            'text_type' => MemberSkillType::BY_USERS,
        ]);
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            /** @var MemberSkill $memberSkill */
            foreach ($data['memberSkills'] as $memberSkill) {
                $memberSkill->setEvaluateAt(new DateTimeImmutable());
            }
            $this->entityManager->flush();
        }

        return $this->render('cluster/admin/skill_assess.modal.html.twig', [
            'title' => 'Évaluations',
            'content' => $skill->getContent(),
            'form' => $form->createView(),
        ]);
    }
}
