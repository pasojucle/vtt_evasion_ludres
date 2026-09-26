<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Core\Handler\ActionFormHandler;
use App\Core\Handler\DetailHandler;
use App\Dto\Payload\AssociateResourcePayload;
use App\Dto\Payload\ClusterSkillAddPayload;
use App\Entity\Cluster;
use App\Entity\Enum\EvaluationEnum;
use App\Entity\Member;
use App\Entity\MemberSkill;
use App\Entity\Skill;
use App\Form\Admin\ClusterSkillAddType;
use App\Form\Admin\MemberSkillCollectionType;
use App\Form\Admin\MemberSkillType;
use App\State\ClusterSkill\Processor\ClusterSkillAddProcessor;
use App\State\ClusterSkill\Processor\ClusterSkillDeleteProcessor;
use App\State\ClusterSkill\Provider\ClusterSkillAddProvider;
use App\State\ClusterSkill\Provider\ClusterSkillDeleteProvider;
use App\State\ClusterSkill\Provider\ClusterSkillReadProvider;
use App\UseCase\Skill\GetUserSkillCluster;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ClusterSkillController extends AbstractCrudController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private readonly GetUserSkillCluster $getUserSkillCluster,
    ) {
    }

    #[Route('/admin/groupe/evaluations/{cluster}', name: 'admin_cluster_skills', methods: ['GET', 'POST'])]
    #[IsGranted('BIKE_RIDE_LIST')]
    public function adminClusterEvaluations(
        Request $request,
        ClusterSkillReadProvider $provider,
        Cluster $cluster,
        DetailHandler $handler,
    ): Response {
        return $handler->handle($request, $provider, $cluster);
    }

    #[Route('/admin/groupe/evaluation/add/{cluster}', name: 'admin_cluster_skill_add', methods: ['GET', 'POST'])]
    #[IsGranted('BIKE_RIDE_LIST')]
    public function adminClusterEvaluationAdd(
        Request $request,
        ClusterSkillAddProvider $provider,
        ClusterSkillAddProcessor $processor,
        Cluster $cluster,
        ActionFormHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            new ClusterSkillAddPayload($cluster),
            $provider,
            $processor,
            ClusterSkillAddType::class,
        );
    }

    #[Route('/admin/groupe/evaluation/delete/{cluster}/{skill}', name: 'admin_cluster_skill_delete', methods: ['GET', 'POST'])]
    #[IsGranted('BIKE_RIDE_LIST')]
    public function adminClusterEvaluationdDelete(
        Request $request,
        ClusterSkillDeleteProcessor $processor,
        ClusterSkillDeleteProvider $provider,
        Cluster $cluster,
        Skill $skill,
    ): Response {
        return $this->handleFormComponentAction(
            $request,
            new AssociateResourcePayload($cluster, $skill),
            $provider,
            $processor
        );
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

    #[Route('/admin/cluster/member/skill/{member}/{skill}/{evaluation}', name: 'admin_cluster_member_skill_add', methods: ['GET', 'POST'])]
    #[IsGranted('BIKE_RIDE_LIST')]
    public function adminClusterMemberSkillAdd(
        Member $member,
        Skill $skill,
        EvaluationEnum $evaluation,
    ) {
    }

    #[Route('/admin/cluster/member_skill/{memberSkill}/{evaluation}', name: 'admin_cluster_member_skill_edit', methods: ['GET', 'POST'])]
    #[IsGranted('BIKE_RIDE_LIST')]
    public function adminClusterMemberSkillEdit(
        MemberSkill $memberSkill,
        EvaluationEnum $evaluation,
    ) {
    }
}
