<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\SkillDtoTransformer;
use App\Entity\Member;
use App\Entity\MemberSkill;
use App\Form\Admin\MemberSkillAddType;
use App\Form\Admin\MemberSkillCollectionType;
use App\Form\Admin\MemberSkillType;
use App\Form\Admin\SkillFilterType;
use App\Repository\MemberSkillRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Turbo\TurboBundle;

#[Route(path: '/admin/membre/competences', name: 'admin_member_skill_')]
class MemberSkillController extends AbstractController
{
    public function __construct(
        private MemberSkillRepository $memberSkillRepository,
        private SkillDtoTransformer $skillDtoTransformer,
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[Route(path: '/edit/{member}', name: 'edit', methods: ['GET', 'POST'], options: ['expose' => true])]
    #[IsGranted('USER_EDIT', 'member')]
    public function edit(
        Request $request,
        Member $member
    ): Response {
        $formFilter = $this->createForm(SkillFilterType::class);
        $formFilter->handleRequest($request);
        $memberSkills = $this->memberSkillRepository->findByMember(
            $member,
            $formFilter->get('skillCategory')->getData(),
            $formFilter->get('level')->getData(),
        );

        $form = $this->createForm(MemberSkillCollectionType::class, ['memberSkills' => new ArrayCollection($memberSkills)], [
            'action' => $request->getUri(),
            'text_type' => MemberSkillType::BY_SKILLS,
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

        return $this->render('member_skill/admin/edit.html.twig', [
            'form' => $form->createView(),
            'formFilter' => $formFilter->createView(),
            'member' => $member,
        ]);
    }

    #[Route(path: '/add/{member}', name: 'add', methods: ['GET', 'POST'], options: ['expose' => true])]
    #[IsGranted('SKILL_ADD')]
    public function add(
        Request $request,
        Member $member
    ): Response {
        $response = new Response("OK", Response::HTTP_OK);
        $form = $this->createForm(MemberSkillAddType::class, null, [
            'action' => $request->getUri(),
            'memberId' => $member->getId(),
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted()) {
            if ($form->isValid()) {
                $skill = $form->get('skill')->getData();
                $member->addMemberSkill($skill);
                $this->entityManager->flush();

                if ($request->getPreferredFormat() === TurboBundle::STREAM_FORMAT) {
                    $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
                    
                    return $this->render('cluster/admin/skill_added.stream.html.twig', [
                        'skill' => $this->skillDtoTransformer->fromEntity($skill),
                        'member' => $member,
                    ]);
                }

                return $this->redirectToRoute('admin_membver_skill_edit', ['member' => $member->getId()]);
            }
            $response = new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->render('member_skill/admin/skill_add.modal.html.twig', [
            'form' => $form->createView(),
            'member' => $member,
        ], $response);
    }
}
