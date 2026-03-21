<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\Member;
use App\Form\Admin\HealthType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class HealthController extends AbstractController
{
    #[Route('/admin/sante/edit/{member}', name: 'admin_health_edit', methods: ['GET', 'POST'])]
    #[IsGranted('MEMBER_EDIT', 'member')]
    public function adminEdit(
        Request $request,
        UserDtoTransformer $userDtoTransformer,
        EntityManagerInterface $entityManager,
        Member $member
    ): Response {
        $form = $this->createForm(HealthType::class, $member->getHealth());
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $health = $form->getData();
            $entityManager->persist($health);
            $entityManager->flush();

            return $this->redirectToRoute('admin_user', [
                'user' => $member->getId(),
            ]);
        }

        return $this->render('health/admin/edit.html.twig', [
            'user' => $userDtoTransformer->fromEntity($member),
            'form' => $form->createView(),
        ]);
    }
}
