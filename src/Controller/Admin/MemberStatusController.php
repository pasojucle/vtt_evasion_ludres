<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Core\Handler\ActionFormHandler;
use App\Core\Handler\DetailHandler;
use App\Entity\Member;
use App\Form\Admin\MemberStatusType;
use App\State\MemberStatus\Processor\MemberStatusUpdateProcessor;
use App\State\MemberStatus\Provider\MemberStatusReadProvider;
use App\State\MemberStatus\Provider\MemberStatusUpdateProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/member-satus', name: 'admin_member_status')]
class MemberStatusController extends AbstractCrudController
{
    #[Route('/{member}', name: '_show', methods: ['GET'])]
    #[IsGranted('USER_EDIT', 'member')]
    public function show(
        Request $request,
        MemberStatusReadProvider $provider,
        Member $member,
        DetailHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $provider,
            $member,
        );
    }
    
    #[Route('/edit/{member}', name: '_edit', methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'member')]
    public function adminEdit(
        Request $request,
        MemberStatusUpdateProvider $provider,
        MemberStatusUpdateProcessor $processor,
        Member $member,
        ActionFormHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $member,
            $provider,
            $processor,
            MemberStatusType::class
        );
    }
}
