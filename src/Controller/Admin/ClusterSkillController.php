<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\SkillDtoTransformer;
use App\Entity\Cluster;
use App\Entity\Skill;
use App\Form\Admin\SkillAddType;
use App\Repository\SkillRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    ) {
    }

    #[Route('/admin/groupe/evaluations/{cluster}', name: 'admin_cluster_evaluations', methods: ['GET', 'POST'])]
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

    #[Route('/admin/groupe/evaluation/add/{cluster}', name: 'admin_cluster_evaluation_add', methods: ['GET', 'POST'])]
    #[IsGranted('BIKE_RIDE_LIST')]
    public function adminClusterEvaluationAdd(
        Cluster $cluster,
        Request $request,
    ): Response {
        $response = new Response("OK", Response::HTTP_OK);
        $form = $this->createForm(SkillAddType::class, null, [
            'action' => $request->getUri(),
            'clusterId' => $cluster->getId(),
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted()) {
            if ($form->isValid()) {
                $skill = $form->get('skill')->getData();
                dump($skill);
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

                return $this->redirectToRoute('admin_cluster_evaluations', ['cluster' => $cluster->getId()]);
            }
            $response = new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->render('cluster/admin/skill_add.modal.html.twig', [
            'form' => $form->createView(),
            'cluster' => $cluster,
        ], $response);
    }

    #[Route('/admin/groupe/evaluation/delete/{skill}', name: 'admin_cluster_evaluation_delete', methods: ['GET', 'POST'])]
    #[IsGranted('BIKE_RIDE_LIST')]
    public function adminClusterEvaluationdDelete(
        Skill $skill,
        Request $request,
    ): Response {

    }

    #[Route('/admin/groupe/evaluation/eval/{skill}', name: 'admin_cluster_evaluation_eval', methods: ['GET', 'POST'])]
    #[IsGranted('BIKE_RIDE_LIST')]
    public function adminClusterEvaluationdEval(
        Skill $skill,
        Request $request,
    ): Response {

    }
}