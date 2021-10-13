<?php

namespace App\Controller;

use DateTime;
use App\Entity\Licence;
use App\Form\Admin\LicenceValidateType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\Licence\LicenceValidateService;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LicenceController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/admin/delete/licence/{licence}", name="admin_delete_licence")
     */
    public function adminDeleteLicence(
        Request $request,
        Licence $licence
    ): Response
    {
        $user = $licence->getUser();
        $fullName = $user->getFirstIdentity()->getName().' '.$user->getFirstIdentity()->getFirstName();
        $form = $this->createForm(FormType::class, null, [
            'action' => $this->generateUrl('admin_delete_licence', 
                [
                    'licence'=> $licence->getId(),
                ]
            ),
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('post') && $form->isSubmitted() && $form->isValid()) {
            $this->entityManager->remove($licence);
            $this->entityManager->flush();

            $this->addFlash('success', "La licence de l'utilisateur $fullName a bien été supprimée");

            return $this->redirectToRoute('admin_registrations', ['filtered' => true, 'p' => $request->query->get('p')]);
        }

        return $this->render('licence/admin/delete.modal.html.twig', [
            'licence'=> $licence,
            'fullname' => $fullName,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/inscription/validate/{licence}", name="admin_registration_validate")
     */
    public function adminRegistartionValidate(
        Request $request,
        LicenceValidateService $licenceValidateService,
        Licence $licence
    ): Response
    {
        $user = $licence->getUser();
        $fullName = $user->getFirstIdentity()->getName().' '.$user->getFirstIdentity()->getFirstName();
        $data = ['licenceNumber' => $user->getLicenceNumber(), 'medicalCertificateDate' => $user->getHealth()->getMedicalCertificateDate()];
        $form = $this->createForm(LicenceValidateType::class, $data, [
            'is_final' => $licence->isFinal(),
            'licences' => $user->getLicences(),
        ]);
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $licenceValidateService->execute($request,$licence);

            $this->addFlash('success', "La licence de l'utilisateur $fullName a bien été validée");

            return $this->redirectToRoute('admin_registrations', ['filtered' => true, 'p' => $request->query->get('p')]);
        }
        return $this->render('licence/admin/validate.modal.html.twig', [
            'form' => $form->createView(),
            'licence'=> $licence,
            'fullname' => $fullName,
        ]);
    }
}
