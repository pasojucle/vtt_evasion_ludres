<?php

declare(strict_types=1);

namespace App\Controller\Admin;


use App\Entity\Member;
use App\Form\Admin\MemberStatusType;
use App\State\MemberStatus\Processor\MemberStatusUpdateProcessor;
use App\State\MemberStatus\Provider\MemberStatusReadProvider;
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
        MemberStatusReadProvider $provider,
        Member $member,
    ): Response {
        
        return $this->render('member_status/admin/show.html.twig', [
            'view' => $provider->getStreamView($member),
        ]);
    }
    
    #[Route('/edit/{member}', name: '_edit', methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'member')]
    public function adminEdit(
        Request $request,
        MemberStatusReadProvider $provider,
        MemberStatusUpdateProcessor $processor,
        Member $member,
    ): Response {

        return $this->handleFormComponentAction(
            $request,
            $member,
            $provider,
            $processor,
            MemberStatusType::class
        );
    }
}
