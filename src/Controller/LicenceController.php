<?php

namespace App\Controller;

use App\Entity\Licence;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
     * @Route("/admin/validate/licence/{licence}", name="admin_validate_licence")
     */
    public function adminValidateLicence(
        Request $request,
        Licence $licence
    ): Response
    {
        $user = $licence->getUser();
        $fullName = $user->getFirstIdentity()->getName().' '.$user->getFirstIdentity()->getFirstName();
        $status = ($licence->isFinal()) ? Licence::STATUS_VALID : Licence::STATUS_TESTING;
        $licence->setStatus($status);
        $this->entityManager->persist($licence);
        $this->entityManager->flush();

        $this->addFlash('success', "La licence de l'utilisateur $fullName a bien été validée");

        return $this->redirectToRoute('admin_registrations', ['filtered' => true, 'p' => $request->query->get('p')]);
    }
}
