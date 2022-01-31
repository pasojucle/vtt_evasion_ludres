<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\IdentitiesType;
use App\Repository\IdentityRepository;
use App\UseCase\Identity\EditIdentity;
use App\ViewModel\UserPresenter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IdentityController extends AbstractController
{
    /**
     * @Route("/admin/identite/edit/{user}/{isKinship}", name="admin_identity_edit", defaults={"isKinship"=0})
     */
    public function adminEdit(
        Request $request,
        IdentityRepository $identityRepository,
        UserPresenter $presenter,
        EditIdentity $editIdentity,
        User $user,
        bool $isKinship
    ): Response {
        $licence = $user->getLastLicence();
        if (! $isKinship) {
            $identity = $identityRepository->findMemberByUser($user);
            $identities = [$identity];
        } else {
            $identities = $identityRepository->findKinShipsByUser($user);
        }
        $form = $this->createForm(IdentitiesType::class, [
            'identities' => $identities,
        ], [
            'category' => $licence->getCategory(),
            'season_licence' => $licence,
            'is_kinship' => $isKinship,
        ]);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $editIdentity->execute($request, $user, $form);

            return $this->redirectToRoute('admin_user', [
                'user' => $user->getId(),
            ]);
        }
        $presenter->present($user);

        return $this->render('identity/edit.html.twig', [
            'user' => $presenter->viewModel(),
            'form' => $form->createView(),
        ]);
    }
}
