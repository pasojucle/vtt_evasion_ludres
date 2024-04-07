<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\Licence;
use App\UseCase\Coverage\GetCoveragesFiltered;
use App\UseCase\Coverage\ValidateCoverage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/assurance', name: 'admin_coverage')]
class CoverageController extends AbstractController
{
    #[Route('s/{filtered}', name: '_list', methods: ['GET', 'POST'], defaults:['filtered' => 0])]
    #[IsGranted('USER_LIST')]
    public function list(
        GetCoveragesFiltered $getCoveragesFiltered,
        Request $request,
        bool $filtered
    ): Response {
        return $this->render('coverage/admin/list.html.twig',
            $getCoveragesFiltered->list($request, $filtered)
        );
    }

    #[Route('validate/{licence}', name: '_validate', methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'licence')]
    public function adminRegistartionValidate(
        Request $request,
        ValidateCoverage $validateCoverage,
        UserDtoTransformer $userDtoTransformer,
        Licence $licence
    ): Response {
        $userDto = $userDtoTransformer->fromEntity($licence->getUser());
        $fullName = $userDto->member->fullName;
        $form = $this->createForm(FormType::class, null, [
            'action' => $this->generateUrl(
                'admin_coverage_validate',
                [
                    'licence' => $licence->getId(),
                ]
            ),
        ]);
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $validateCoverage->execute($request, $licence);

            $this->addFlash('success', "L'assance de {$fullName} a bien été validée");

            return $this->redirectToRoute('admin_coverage_list', [
                'filtered' => true,
                'p' => $request->query->get('p'),
            ]);
        }

        return $this->render('coverage/admin/validate.modal.html.twig', [
            'form' => $form->createView(),
            'licence' => $licence,
            'fullname' => $fullName,
        ]);
    }

    #[Route('/export', name: 's_export', methods: ['GET'])]
    #[IsGranted('USER_LIST')]
    public function adminCoveragesExport(
        GetCoveragesFiltered $getCoveragesFiltered,
        Request $request
    ): Response {
        return $getCoveragesFiltered->export($request);
    }

    #[Route('/emails', name: 's_email_to_clipboard', methods: ['GET'])]
    #[IsGranted('USER_LIST')]
    public function adminEmailCoverages(
        GetCoveragesFiltered $getCoveragesFiltered,
        Request $request
    ): JsonResponse {
        return new JsonResponse($getCoveragesFiltered->emailsToClipboard($request));
    }

    #[Route('/autocomplete', name: '_autocomplete', methods: ['GET'])]
    #[IsGranted('USER_SHARE')]
    public function memberAutocomplete(
        GetCoveragesFiltered $getCoveragesFiltered,
        Request $request
    ): JsonResponse {
        return new JsonResponse(['results' => $getCoveragesFiltered->choices($request->query->all())]);
    }
}
