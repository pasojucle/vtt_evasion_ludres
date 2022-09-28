<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Licence;
use App\UseCase\Coverage\GetCoveragesFiltered;
use App\UseCase\Coverage\ValidateCoverage;
use App\ViewModel\UserPresenter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/assurance', name: 'admin_coverage')]
class CoverageController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('s/{filtered}', name: '_list', methods: ['GET', 'POST'], defaults:['filtered' => 0])]
    public function list(
        GetCoveragesFiltered $getCoveragesFiltered,
        Request $request,
        bool $filtered
    ): Response {
        return $this->render(
            'coverage/admin/list.html.twig',
            $getCoveragesFiltered->list($request, $filtered)
        );
    }

    #[Route('validate/{licence}', name: '_validate', methods: ['GET', 'POST'])]
    public function adminRegistartionValidate(
        Request $request,
        ValidateCoverage $validateCoverage,
        UserPresenter $userPresenter,
        Licence $licence
    ): Response {
        $userPresenter->present($licence->getUser());
        $fullName = $userPresenter->viewModel()->member->fullName;
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

    #[Route('/export/assurances', name: 's_export', methods: ['GET'])]
    public function adminCoveragesExport(
        GetCoveragesFiltered $getCoveragesFiltered,
        Request $request
    ): Response {
        return $getCoveragesFiltered->export($request);
    }

    #[Route('/emails/assurances', name: 's_email_to_clipboard', methods: ['GET'])]
    public function adminEmailCoverages(
        GetCoveragesFiltered $getCoveragesFiltered,
        Request $request
    ): JsonResponse {
        return new JsonResponse($getCoveragesFiltered->emailsToClipboard($request));
    }

    #[Route('/assurance/choices', name: '_choices', methods: ['GET'])]
    public function memberChoices(
        GetCoveragesFiltered $getCoveragesFiltered,
        Request $request
    ): JsonResponse {
        $query = $request->query->get('q');

        $filters = json_decode($request->query->get('filters'), true);

        return new JsonResponse($getCoveragesFiltered->choices($filters, $query));
    }
}
