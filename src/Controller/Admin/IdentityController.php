<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\Identity;
use App\Form\IdentityType;
use App\UseCase\Identity\EditIdentity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class IdentityController extends AbstractController
{
    #[Route('/admin/identite/edit/{identity}', name: 'admin_identity_edit', methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'identity')]
    public function adminEdit(
        Request $request,
        UserDtoTransformer $userDtoTransformer,
        EditIdentity $editIdentity,
        Identity $identity,
    ): Response {
        $user = $identity->getUser();
        $licence = $user->getLastLicence();
        $form = $this->createForm(IdentityType::class, $identity, [
            'category' => $licence->getCategory(),
            'is_yearly' => $licence->getState()->isYearly(),
            'is_gardian' => false,
        ]);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $editIdentity->execute($request, $identity, $form);

            return $this->redirectToRoute('admin_user', [
                'user' => $user->getId(),
            ]);
        }

        return $this->render('identity/edit.html.twig', [
            'user' => $userDtoTransformer->fromEntity($user),
            'form' => $form->createView(),
        ]);
    }
}
