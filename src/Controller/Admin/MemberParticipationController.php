<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Core\Handler\ActionStreamDeleteFilterHandler;
use App\Core\Handler\ActionStreamFilterHandler;
use App\Core\Handler\ListExportHandler;
use App\Core\Handler\ListLoadMoreHandler;
use App\Dto\Filter\MemberParticipationFilter;
use App\Entity\Member;
use App\State\MemberParticipation\Provider\MemberParticipationReadProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/member/participation', name: 'admin_member_participation')]
class MemberParticipationController extends AbstractController
{
    #[Route('/list/{member}', name: '_list', methods: ['GET'])]
    #[IsGranted('USER_EDIT', 'member')]
    public function show(
        Request $request,
        MemberParticipationReadProvider $provider,
        Member $member,
        ListLoadMoreHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            MemberParticipationFilter::class,
            $provider,
            $member
        );
    }

    #[Route('/filter/{member}', name: '_filter', requirements:['member' => '\d+'], methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'member')]
    public function filter(
        Request $request,
        MemberParticipationReadProvider $provider,
        Member $member,
        ActionStreamFilterHandler $handler
    ): Response {
        return $handler->handle(
            $request,
            $member,
            $provider,
        );
    }

    #[Route('/delete/filter/{member}', name: '_filter_delete', requirements:['member' => '\d+'], methods: ['GET'])]
    #[IsGranted('USER_EDIT', 'member')]
    public function deleteFilter(
        Request $request,
        MemberParticipationReadProvider $provider,
        Member $member,
        ActionStreamDeleteFilterHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $member,
            MemberParticipationFilter::class,
            $provider,
        );
    }

    #[Route('/export/{member}', name: '_export', requirements:['member' => '\d+'], methods: ['GET'])]
    #[IsGranted('USER_EDIT', 'member')]
    public function lineChart(
        Request $request,
        MemberParticipationReadProvider $provider,
        Member $member,
        ListExportHandler $handler,
    ): Response {
        
        return $handler->handle(
            $request,
            MemberParticipationFilter::class,
            $provider,
            $member,
        );
    }
}
