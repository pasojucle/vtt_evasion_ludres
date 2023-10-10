<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\Admin\HealthType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Dto\DtoTransformer\UserDtoTransformer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HealthController extends AbstractController
{
    #[Route('/admin/sante/edit/{user}', name: 'admin_health_edit', methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'user')]
    public function adminEdit(
        Request $request,
        UserDtoTransformer $userDtoTransformer,
        EntityManagerInterface $entityManager,
        User $user
    ): Response {
        $form = $this->createForm(HealthType::class, $user->getHealth());
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $health = $form->getData();
            $entityManager->persist($health);
            $entityManager->flush();

            return $this->redirectToRoute('admin_user', [
                'user' => $user->getId(),
            ]);
        }

        return $this->render('health/admin/edit.html.twig', [
            'user' => $userDtoTransformer->fromEntity($user),
            'form' => $form->createView(),
        ]);
    }
}
