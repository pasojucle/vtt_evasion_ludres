<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Core\Handler\ActionDirectHandler;
use App\Core\Handler\ActionFormHandler;
use App\Core\Handler\ActionStreamDeleteFilterHandler;
use App\Core\Handler\ActionStreamFilterHandler;
use App\Core\Handler\ListLoadMoreHandler;
use App\Dto\Filter\MemberSkillFilter;
use App\Dto\Payload\MemberSkillCreatePayload;
use App\Dto\Payload\MemberSkillEvaluationPayload;
use App\Dto\State\ViewContext;
use App\Entity\Enum\EvaluationEnum;
use App\Entity\Member;
use App\Entity\MemberSkill;
use App\Form\Admin\MemberSkillAddType;
use App\State\MemberSkill\Processor\MemberSkillCreateProcessor;
use App\State\MemberSkill\Processor\MemberSkillEvaluationProcessor;
use App\State\MemberSkill\Provider\MemberSkillCreateProvider;
use App\State\MemberSkill\Provider\MemberSkillReadProvider;
use App\State\MemberSkill\Provider\MemberSkillUpdateProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/admin/membre/competences', name: 'admin_member_skill')]
class MemberSkillController extends AbstractCrudController
{
    #[Route('/list/{member}', name: '_list', methods: ['GET'])]
    #[IsGranted('USER_EDIT', 'member')]
    public function show(
        Request $request,
        MemberSkillReadProvider $provider,
        Member $member,
        ListLoadMoreHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            MemberSkillFilter::class,
            $provider,
            $member,
        );
    }

    #[Route('/filter/{member}', name: '_filter', methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'member')]
    public function filter(
        Request $request,
        MemberSkillReadProvider $provider,
        Member $member,
        ActionStreamFilterHandler $handler
    ): Response {
        return $handler->handle(
            $request,
            $member,
            $provider,
        );
    }

    #[Route('/delete/filter/{member}', name: '_filter_delete', methods: ['GET'])]
    #[IsGranted('USER_EDIT', 'member')]
    public function deleteFilter(
        Request $request,
        MemberSkillReadProvider $provider,
        Member $member,
        ActionStreamDeleteFilterHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $member,
            MemberSkillFilter::class,
            $provider,
        );
    }

    #[Route(path: '/edit/{memberSkill}/{evaluation}', name: '_edit', methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'memberSkill')]
    public function edit(
        Request $request,
        MemberSkillUpdateProvider $provider,
        MemberSkillEvaluationProcessor $processor,
        MemberSkill $memberSkill,
        EvaluationEnum $evaluation,
        ActionDirectHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            new MemberSkillEvaluationPayload($memberSkill, $evaluation),
            $provider,
            $processor,
        );
    }

    #[Route(path: '/add/{member}', name: '_add', methods: ['GET', 'POST'])]
    #[IsGranted('SKILL_ADD')]
    public function add(
        Request $request,
        MemberSkillCreateProvider $provider,
        MemberSkillCreateProcessor $processor,
        Member $member,
        ActionFormHandler $handler,
    ): Response {
        $queryParams = $request->query->all();
        $filter = $provider->getHydratedDto($queryParams, MemberSkillFilter::class);

        return $handler->handle(
            $request,
            new MemberSkillCreatePayload(
                $member,
                null,
                $filter->category,
                $filter->level,
            ),
            $provider,
            $processor,
            MemberSkillAddType::class,
            $member,
        );
    }
}
