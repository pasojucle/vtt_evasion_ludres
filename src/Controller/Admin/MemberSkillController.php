<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\SkillDtoTransformer;
use App\Dto\Filter\MemberSkillFilter;
use App\Dto\Payload\MemberSkillCreatePayload;
use App\Dto\Payload\MemberSkillEvaluationPayload;
use App\Dto\State\TurboStreamContext;
use App\Entity\Enum\EvaluationEnum;
use App\Entity\Member;
use App\Entity\MemberSkill;
use App\Form\Admin\MemberSkillAddType;
use App\State\MemberSkill\Processor\MemberSkillCreateProcessor;
use App\State\MemberSkill\Processor\MemberSkillEvaluationProcessor;
use App\State\MemberSkill\Provider\MemberSkillCreateProvider;
use App\State\MemberSkill\Provider\MemberSkillReadProvider;
use App\State\MemberSkill\Provider\MemberSkillUpdateProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/admin/membre/competences', name: 'admin_member_skill')]
class MemberSkillController extends AbstractCrudController
{
    public function __construct(
        private SkillDtoTransformer $skillDtoTransformer,
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/list/{member}', name: '_list', methods: ['GET'])]
    #[IsGranted('USER_EDIT', 'member')]
    public function show(
        Request $request,
        MemberSkillReadProvider $provider,
        Member $member,
    ): Response {

        return $this->handleListLoadMoreAction(
            $request,
            MemberSkillFilter::class,
            $provider,
            new TurboStreamContext(
                $request->attributes->get('_route'),
                $request->query->getInt('page', 1),
                $member
            )
        );
    }

    #[Route('/filter/{member}', name: '_filter', methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'member')]
    public function filter(
        Request $request,
        MemberSkillReadProvider $provider,
        Member $member,
    ): Response {
        return $this->handleStreamFilterAction(
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
    ): Response {
        return $this->handleStreamFilterDeleteAction(
            $request,
            $member,
            $provider,
            'admin_member_skill_filter'
        );
    }

    #[Route(path: '/edit/{memberSkill}/{evaluation}', name: '_edit', methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'memberSkill')]
    public function edit(
        Request $request,
        MemberSkillUpdateProvider $provider,
        MemberSkillEvaluationProcessor $processor,
        MemberSkill $memberSkill,
        EvaluationEnum $evaluation
    ): Response {
        $token = $request->query->get('csrfToken');
        $result = $processor->process(
            new MemberSkillEvaluationPayload($memberSkill, $evaluation, $token),
            null,
        );

        $streamView = ($result->success)
            ? $provider->getStreamView($memberSkill)
            : $result->flashMessages;

        return $this->render($streamView->getStreamTemplate(),[ 
                'view' => $streamView, 
            ], new Response('', Response::HTTP_OK, [
                'Content-Type' => 'text/vnd.turbo-stream.html',
            ]));
    }

    #[Route(path: '/add/{member}', name: '_add', methods: ['GET', 'POST'])]
    #[IsGranted('SKILL_ADD')]
    public function add(
        Request $request,
        MemberSkillCreateProvider $provider,
        MemberSkillCreateProcessor $processor,
        Member $member
    ): Response {

        return $this->handleFormComponentAction(
            $request,
            new MemberSkillCreatePayload($member),
            $provider,
            $processor,
            MemberSkillAddType::class
        );
    }
}
