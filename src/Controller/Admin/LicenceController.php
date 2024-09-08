<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\Licence;
use App\Form\Admin\LicenceValidateType;
use App\UseCase\Licence\ValidateLicence;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class LicenceController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Route('/admin/inscription/delete/{licence}', name: 'admin_delete_licence', methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'licence')]
    public function adminDeleteLicence(
        Request $request,
        UserDtoTransformer $userDtoTransformer,
        Licence $licence
    ): Response {
        $userDto = $userDtoTransformer->fromEntity($licence->getUser());
        $fullName = $userDto->member->fullName;
        $form = $this->createForm(FormType::class, null, [
            'action' => $this->generateUrl(
                'admin_delete_licence',
                [
                    'licence' => $licence->getId(),
                ]
            ),
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('post') && $form->isSubmitted() && $form->isValid()) {
            foreach($licence->getLicenceSwornCertifications() as $licenceSwornCertification) {
                $this->entityManager->remove($licenceSwornCertification);
            }
            $this->entityManager->remove($licence);
            $this->entityManager->flush();

            $this->addFlash('success', "La licence de l'utilisateur {$fullName} a bien été supprimée");

            return $this->redirectToRoute('admin_registrations', [
                'filtered' => true,
                'p' => $request->query->get('p'),
            ]);
        }

        return $this->render('licence/admin/delete.modal.html.twig', [
            'licence' => $licence,
            'fullname' => $fullName,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/inscription/validate/{licence}', name: 'admin_registration_validate', methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'licence')]
    public function adminRegistartionValidate(
        Request $request,
        ValidateLicence $validateLicence,
        Licence $licence
    ): Response {
        $user = $licence->getUser();
        $fullName = $user->getFirstIdentity()->getName() . ' ' . $user->getFirstIdentity()->getFirstName();
        $data = [
            'licenceNumber' => $user->getLicenceNumber(),
            'medicalCertificateDate' => $user->getHealth()->getMedicalCertificateDate(),
        ];
        $form = $this->createForm(LicenceValidateType::class, $data, [
            'is_final' => $licence->isFinal(),
            'licences' => $user->getLicences(),
        ]);
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $validateLicence->execute($request, $licence);

            $this->addFlash('success', "La licence de l'utilisateur {$fullName} a bien été validée");

            return $this->redirectToRoute('admin_registrations', [
                'filtered' => true,
                'p' => $request->query->get('p'),
            ]);
        }

        return $this->render('licence/admin/validate.modal.html.twig', [
            'form' => $form->createView(),
            'licence' => $licence,
            'fullname' => $fullName,
        ]);
    }
}
