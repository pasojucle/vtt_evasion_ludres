<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\User;
use App\Form\IdentitiesType;
use App\Repository\IdentityRepository;
use App\UseCase\Identity\EditIdentity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IdentityController extends AbstractController
{
    #[Route('/admin/identite/edit/{user}/{isKinship}', name: 'admin_identity_edit', methods: ['GET', 'POST'], defaults:['isKinship' => 0])]
    public function adminEdit(
        Request $request,
        IdentityRepository $identityRepository,
        UserDtoTransformer $userDtoTransformer,
        EditIdentity $editIdentity,
        User $user,
        bool $isKinship
    ): Response {
        $licence = $user->getLastLicence();
        if (!$isKinship) {
            $identity = $identityRepository->findMemberByUser($user);
            $identities = [$identity];
        } else {
            $identities = $identityRepository->findKinShipsByUser($user);
        }
        $form = $this->createForm(IdentitiesType::class, ['identities' => $identities], [
            'category' => $licence->getCategory(),
            'is_final' => $licence->isFinal(),
            'is_kinship' => $isKinship,
        ]);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $editIdentity->execute($request, $user, $form);

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
