<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\Member;
use App\Form\GardiansType;
use App\Service\GardianService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class GardianController extends AbstractController
{
    #[Route('/admin/responsables/edit/{member}', name: 'admin_gardians_edit', methods: ['GET', 'POST'])]
    #[IsGranted('MEMBER_EDIT', 'member')]
    public function adminEdit(
        Request $request,
        UserDtoTransformer $userDtoTransformer,
        GardianService $gardianService,
        Member $member,
    ): Response {
        $licence = $member->getLastLicence();
        $form = $this->createForm(GardiansType::class, $member, [
            'category' => $licence->getCategory(),
            'is_yearly' => $licence->getState()->isYearly(),
        ]);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $gardianService->setAddress($member);

            return $this->redirectToRoute('admin_user', [
                'user' => $member->getId(),
            ]);
        }

        return $this->render('gardian/edit.html.twig', [
            'user' => $userDtoTransformer->fromEntity($member),
            'form' => $form->createView(),
        ]);
    }
}
