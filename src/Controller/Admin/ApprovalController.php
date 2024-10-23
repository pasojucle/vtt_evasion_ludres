<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\ApprovalDtoTransformer;
use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\User;
use App\Entity\Approval;
use App\Form\ApprovalType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/autorisation/', name: 'admin_approval_')]
class ApprovalController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserDtoTransformer $userDtoTransformer,
        private readonly ApprovalDtoTransformer $approvalDtoTransformer,
    )
    {
        
    }

    #[Route('edit/{approval}', name: 'edit', methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'approval')]
    public function edit(Request $request, Approval $approval): Response
    {
        /** @var User $user */
        $user = $approval->getUser();
        $form = $this->createForm(ApprovalType::class, $approval, [
            'action' => $request->getUri(),
        ]);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_user', [
                'user' => $user->getId(),
            ]);
        }

        return $this->render('approval/admin/edit.html.twig', [
            'user' => $this->userDtoTransformer->fromEntity($user),
            'approval' => $this->approvalDtoTransformer->fromEntity($approval),
            'form' => $form->createView(),
        ]);
    }

}