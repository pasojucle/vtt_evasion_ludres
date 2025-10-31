<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\Licence;
use App\Form\Admin\LicenceRegisterType;
use App\Service\LicenceService;
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
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            foreach ($licence->getLicenceSwornCertifications() as $licenceSwornCertification) {
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

    #[Route('/admin/inscription/receive/{licence}', name: 'admin_registration_receive', methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'licence')]
    public function adminRegistartionValidate(
        Request $request,
        LicenceService $licenceService,
        Licence $licence
    ): Response {
        $user = $licence->getUser();
        $fullName = $user->getFirstIdentity()->getName() . ' ' . $user->getFirstIdentity()->getFirstName();

        $form = $this->createForm(FormType::class, null, [
            'action' => $request->getUri(),
        ]);
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $tansition = ($licence->getState()->isYearly()) ? 'receive_yearly_file' : 'receive_trial_file';
            if ($licenceService->applyTransition($licence, $tansition)) {
                $this->entityManager->flush();

                $this->addFlash('success', "Le dossier de {$fullName} a bien été reçu");

                return $this->redirectToRoute('admin_registrations', [
                    'filtered' => true,
                    'p' => $request->query->get('p'),
                ]);
            }
 
            $this->addFlash('success', "Une erreur est survenue lors de la réception du dossier de {$fullName}");
        }

        return $this->render('licence/admin/receive.modal.html.twig', [
            'form' => $form->createView(),
            'licence' => $licence,
            'fullname' => $fullName,
            'message' => ($licence->getState()->isYearly())
                ? 'Réception du dossier d\'inscription signé avec le paiement.'
                : 'Réception du dossier d\'inscription signé'
        ]);
    }

    #[Route('/admin/inscription/register/{licence}', name: 'admin_registration_register', methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'licence')]
    public function adminRegistartionRegister(
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
        $form = $this->createForm(LicenceRegisterType::class, $data, [
            'is_yearly' => $licence->getState()->isYearly(),
            'licences' => $user->getLicences(),
        ]);
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $validateLicence->execute($request, $licence);

            $this->addFlash('success', "Le dossier de {$fullName} a bien été inscrit à la fédération");

            return $this->redirectToRoute('admin_registrations', [
                'filtered' => true,
                'p' => $request->query->get('p'),
            ]);
        }

        return $this->render('licence/admin/register.modal.html.twig', [
            'form' => $form->createView(),
            'licence' => $licence,
            'fullname' => $fullName,
        ]);
    }
}
