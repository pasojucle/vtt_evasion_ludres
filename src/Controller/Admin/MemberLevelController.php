<?php

declare(strict_types=1);

namespace App\Controller\Admin;


use App\Entity\Member;
use App\Form\Admin\MemberLevelType;
use App\State\MemberLevel\Processor\MemberLevelUpdateProcessor;
use App\State\MemberLevel\Provider\MemberLevelReadProvider;
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
        MemberLevelReadProvider $provider,
        Member $member,
    ): Response {
        
        return $this->render('member_level/admin/show.html.twig', [
            'view' => $provider->getStreamView($member),
        ]);
    }
    
    #[Route('/edit/{member}', name: '_edit', methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'member')]
    public function adminEdit(
        Request $request,
        MemberLevelReadProvider $provider,
        MemberLevelUpdateProcessor $processor,
        Member $member,
    ): Response {

        return $this->handleFormComponentAction(
            $request,
            $member,
            $provider,
            $processor,
            MemberLevelType::class
        );
    }
}
