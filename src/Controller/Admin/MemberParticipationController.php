<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\Filter\MemberParticipationFilter;
use App\Entity\Member;
use App\Form\Admin\MemberParticipationType;
use App\State\MemberParticipation\Processor\MemberParticipationProcessor;
use App\State\MemberParticipation\Provider\MemberParticipationReadProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/member/participation', name: 'admin_member_participation')]
class MemberParticipationController extends AbstractCrudController
{
    #[Route('/list/{member}', name: '_list', methods: ['GET'])]
    #[IsGranted('USER_EDIT', 'member')]
    public function show(
        Request $request,
        MemberParticipationReadProvider $provider,
        Member $member,
    ): Response {
        $filter = $provider->getHydratedDto($request->query->all(), MemberParticipationFilter::class);
        $filter->member = $member;

        return $this->render('member_participation/admin/show.html.twig', [
            'view' => $provider->getStreamView($filter, [
                'filter' => $filter,
                'route' => $request->attributes->get('_route'),
                'page' => $request->query->getInt('page', 1),
            ]),
        ]);
    }

    #[Route('/filter/{member}', name: '_filter', methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'member')]
    public function filter(
        Request $request,
        MemberParticipationreadProvider $provider,
        MemberParticipationProcessor $processor,
        Member $member,
    ): Response {
        $filter = $provider->getHydratedDto($request->query->all(), MemberParticipationFilter::class);
        $filter->member = $member;

        return $this->handleFormComponentAction(
            $request,
            $filter,
            $provider,
            $processor,
            MemberParticipationType::class,
            [
                'route' => $request->attributes->get('_route'),
                'page' => $request->query->getInt('page', 1),
            ]
        );
    }
}
