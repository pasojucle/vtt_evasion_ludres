<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\LicenceAgreementDtoTransformer;
use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\LicenceAgreement;
use App\Entity\User;
use App\Form\LicenceAgreementType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/autorisation/licence/', name: 'admin_licence_authorization_')]
class LicenceAuthorizationController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserDtoTransformer $userDtoTransformer,
        private readonly LicenceAgreementDtoTransformer $approvalDtoTransformer,
    ) {
    }

    #[Route('edit/{licenceAuthorization}', name: 'edit', methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'licenceAuthorization')]
    public function edit(Request $request, LicenceAgreement $licenceAuthorization): Response
    {
        /** @var User $user */
        $user = $licenceAuthorization->getLicence()->getUser();
        $form = $this->createForm(LicenceAgreementType::class, $licenceAuthorization, [
            'action' => $request->getUri(),
        ]);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_user', [
                'user' => $user->getId(),
            ]);
        }

        return $this->render('licenceAuthorization/admin/edit.html.twig', [
            'user' => $this->userDtoTransformer->fromEntity($user),
            'approval' => $this->approvalDtoTransformer->fromEntity($licenceAuthorization),
            'form' => $form->createView(),
        ]);
    }
}
