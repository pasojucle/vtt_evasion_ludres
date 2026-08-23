<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\Filter\MemberParticipationFilter;
use App\Dto\State\TurboStreamContext;
use App\Entity\Member;
use App\Form\Filter\MemberParticipationType;
use App\State\MemberParticipation\Processor\MemberParticipationFilterProcessor;
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
        return $this->handleListLoadMoreAction(
            $request,
            MemberParticipationFilter::class,
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
        MemberParticipationReadProvider $provider,
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
        MemberParticipationReadProvider $provider,
        Member $member,
    ): Response {
        return $this->handleStreamFilterDeleteAction(
            $request,
            $member,
            $provider,
            'admin_member_participation_filter'
        );
    }

    #[Route('/export/{member}', name: '_export', methods: ['GET'])]
    #[IsGranted('USER_EDIT', 'member')]
    public function lineChart(
        Request $request,
        MemberParticipationReadProvider $provider,
        Member $member,
    ): Response {
        $request->query->set('member', $member->getId());

        return $this->handleExportAction(
            $request,
            MemberParticipationFilter::class,
            $provider,
            sprintf('export_participation_%s.csv', $member->getLicenceNumber())
        );
    }
}
