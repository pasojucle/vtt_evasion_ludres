<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Core\Handler\ActionFormHandler;
use App\Core\Handler\DetailHandler;
use App\Entity\Member;
use App\Form\Admin\MemberLevelType;
use App\State\MemberLevel\Processor\MemberLevelUpdateProcessor;
use App\State\MemberLevel\Provider\MemberLevelReadProvider;
use App\State\MemberLevel\Provider\MemberLevelUpdateProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/member-level', name: 'admin_member_level')]
class MemberLevelController extends AbstractCrudController
{
    #[Route('/{member}', name: '_show', methods: ['GET'])]
    #[IsGranted('USER_EDIT', 'member')]
    public function show(
        Request $request,
        MemberLevelReadProvider $provider,
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
        MemberLevelUpdateProvider $provider,
        MemberLevelUpdateProcessor $processor,
        Member $member,
        ActionFormHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $member,
            $provider,
            $processor,
            MemberLevelType::class
        );
    }
}
