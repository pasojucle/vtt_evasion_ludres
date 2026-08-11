<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\Filter\MemberParticipationFilter;
use App\Dto\Payload\MemberParticipationFilterPayload;
use App\Entity\Member;
use App\Form\Admin\MemberParticipationType;
use App\State\MemberParticipation\Processor\MemberParticipationFilterProcessor;
use App\State\MemberParticipation\Provider\MemberParticipationFilterProvider;
use App\State\MemberParticipation\Provider\MemberParticipationReadProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/member/participation', name: 'admin_member_participation')]
class MemberParticipationController extends AbstractCrudController
{   
    #[Route('/filter/{member}', name: '_filter', methods: ['GET'])]
    #[IsGranted('USER_EDIT', 'member')]
    public function show(
        Request $request,
        MemberParticipationFilterProvider $provider,
        Member $member,
    ): Response {
        $filter = $provider->getHydratedDto($request->query->all(), MemberParticipationFilter::class);
        $filter->member = $member;

        return $this->render('member_participation/admin/tab_filter.html.twig', [
            'view' => $provider->getStreamView($filter),
        ]);
    }

    #[Route('/filter-edit/{member}', name: '_filter_edit', methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'member')]
    public function filter(
        Request $request,
        MemberParticipationFilterProvider $provider,
        MemberParticipationFilterProcessor $processor,
        Member $member,
    ): Response {
        $filter = $provider->getHydratedDto($request->query->all(), MemberParticipationFilter::class);
        $filter->member = $member;
        dump($filter);

        return $this->handleFormComponentAction(
            $request,
            $filter,
            $provider,
            $processor,
            MemberParticipationType::class
        );
    }
    
    #[Route('/list/{member}', name: '_list', methods: ['GET'])]
    #[IsGranted('USER_EDIT', 'member')]
    public function list(
        Request $request,
        MemberParticipationReadProvider $provider,
        Member $member,
    ): Response {
        $route = $request->attributes->get('_route');
        $filter = $provider->getHydratedDto($request->query->all(), MemberParticipationFilter::class);

        return $this->render('member_participation/admin/activities.html.twig', [
            'view' => $provider->getCollection($member, $filter, $route),
        ]);
    }
}
