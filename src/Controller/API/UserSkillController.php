<?php

declare(strict_types=1);

namespace App\Controller\API;

use App\Dto\DtoTransformer\UserSkillDtoTransformer;
use App\Entity\Member;
use App\Entity\MemberSkill;
use App\Entity\Skill;
use App\Form\Admin\SkillAddType;
use App\Form\Admin\MemberSkillType;
use App\Service\ApiService;
use App\Service\UserSkillService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/user_skill', name: 'api_user_skill_')]
class UserSkillController extends AbstractController
{
    public function __construct(
        private readonly UserSkillDtoTransformer $transformer,
        private readonly EntityManagerInterface $entityManager,
        private readonly ApiService $api,
        private readonly UserSkillService $userSkillService,
    ) {
    }

    #[Route(path: '/list/{user}', name: 'list', methods: ['GET'], requirements:['user' => '\d+'], options: ['expose' => true])]
    public function list(Member $member): JsonResponse
    {
        return new JsonResponse([
            'list' => $this->transformer->fromEntities($member->getUserSkills()),
        ]);
    }

    #[Route(path: '/list_edit/{member}', name: 'list_edit', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function listEdit(Member $member): JsonResponse
    {
        return new JsonResponse([
            'list' => $this->userSkillService->getList($member),
        ]);
    }

    #[Route(path: '/edit/{memberSkill}', name: 'edit', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function edit(Request $request, MemberSkill $memberSkill): JsonResponse
    {
        $form = $this->api->createForm($request, MemberSkillType::class, $memberSkill, [
            'text_type' => MemberSkillType::BY_SKILLS,
        ]);
        
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $userSkill = $form->getData();
            $userSkill->setEvaluateAt(new DateTimeImmutable());
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'data' => [
                    'deleted' => false,
                    'entity' => 'user_skill_edit',
                    'value' => $this->userSkillService->getUserSkill($form->createView()),
                    'sort' => 'idASC',
                ],
            ]);
        }

        return new JsonResponse(['success' => false, ]);
    }

    #[Route(path: '/add/{member}', name: 'add', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function add(Request $request, Member $member): JsonResponse
    {
        $form = $this->api->createForm($request, SkillAddType::class, null, [
            'exclude' => ['entity' => 'user_skill_edit', 'field' => 'skill.value'],
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $skill = $this->entityManager->getRepository(Skill::class)->find($data['skill']);
            $userSkill = new MemberSkill();
            $userSkill->setMember($member)
                ->setSkill($skill);
            $this->entityManager->persist($userSkill);
            $this->entityManager->flush();

            $form = $this->createForm(MemberSkillType::class, $userSkill, [
                'text_type' => MemberSkillType::BY_SKILLS,
                'action' => $this->generateUrl('api_user_skill_edit', ['userSkill' => $userSkill->getId()])
            ]);

            return new JsonResponse([
                'success' => true,
                'data' => [
                    'deleted' => false,
                    'entity' => 'user_skill_edit',
                    'value' => $this->userSkillService->getUserSkill($form->createView()),
                    'sort' => 'idASC',
                ],
            ]);
        }

        return $this->api->renderModal($form, 'Ajouter une compétence', 'Enregistrer');
    }
}
