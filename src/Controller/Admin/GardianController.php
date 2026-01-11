<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\User;
use App\Form\GardiansType;
use App\Service\GardianService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class GardianController extends AbstractController
{
    #[Route('/admin/responsables/edit/{user}', name: 'admin_gardians_edit', methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'user')]
    public function adminEdit(
        Request $request,
        UserDtoTransformer $userDtoTransformer,
        GardianService $gardianService,
        User $user,
    ): Response {
        $licence = $user->getLastLicence();
        $form = $this->createForm(GardiansType::class, $user, [
            'category' => $licence->getCategory(),
            'is_yearly' => $licence->getState()->isYearly(),
        ]);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $gardianService->setAddress($user);

            return $this->redirectToRoute('admin_user', [
                'user' => $user->getId(),
            ]);
        }

        return $this->render('gardian/edit.html.twig', [
            'user' => $userDtoTransformer->fromEntity($user),
            'form' => $form->createView(),
        ]);
    }
}
