<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\Licence;
use App\Form\Admin\LicenceRegisterType;
use App\Form\Admin\LicenceRejectType;
use App\Service\LicenceService;
use App\Service\MailerService;
use App\UseCase\Licence\ValidateLicence;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
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
        Licence $licence
    ): Response {
        $response = new Response("OK", Response::HTTP_OK);
        $user = $licence->getUser();
        $fullName = $user->getIdentity()->getFullName();
        $form = $this->createForm(FormType::class, null, [
            'action' => $request->getUri(),
            'attr' => ['data-action' => 'turbo:submit-end->modal#handleFormSubmit']
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted()) {
            if ($form->isValid()) {
                foreach ($licence->getLicenceAgreements() as $licenceAgreement) {
                    $this->entityManager->remove($licenceAgreement);
                }
                $this->entityManager->remove($licence);
                $this->entityManager->flush();

                $this->addFlash('success', "La licence de l'utilisateur {$fullName} a bien été supprimée");

                return $this->redirectToRoute('admin_registrations', [
                    'filtered' => true,
                    'p' => $request->query->get('p'),
                ]);
            }
            $response = new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->render('component/destructive.modal.html.twig', [
            'title' => 'Supprimer une inscription',
            'content' => sprintf('Etes vous certain de supprimer l\'inscription de %s ?', $fullName),
            'btn_label' => 'Supprimer',
            'form' => $form->createView(),
        ], $response);
    }

    #[Route('/admin/inscription/receive/{licence}', name: 'admin_registration_receive', methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'licence')]
    public function adminRegistartionReceive(
        Request $request,
        LicenceService $licenceService,
        Licence $licence
    ): Response {
        $user = $licence->getUser();
        $fullName = $user->getIdentity()->getFullName();
        $response = new Response("OK", Response::HTTP_OK);
        $form = $this->createForm(FormType::class, null, [
            'action' => $request->getUri(),
            'attr' => ['data-action' => 'turbo:submit-end->modal#handleFormSubmit']
        ]);
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted()) {
            if ($form->isValid()) {
                $tansition = ($licence->getState()->isYearly()) ? 'receive_yearly_file' : 'receive_trial_file';
                if ($licenceService->applyTransition($licence, $tansition)) {
                    $this->entityManager->flush();

                    $this->addFlash('success', "Le dossier de {$fullName} a bien été reçu");

                    return $this->redirectToRoute('admin_registrations', [
                        'filtered' => true,
                        'p' => $request->query->get('p'),
                    ]);
                }
            }
            $response = new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY);
            $this->addFlash('danger', "Une erreur est survenue lors de la réception du dossier de {$fullName}");
        }

        return $this->render('licence/admin/receive.modal.html.twig', [
            'form' => $form->createView(),
            'licence' => $licence,
            'fullname' => $fullName,
            'message' => ($licence->getState()->isYearly())
                ? 'Réception du dossier d\'inscription signé avec le paiement.'
                : 'Réception du dossier d\'inscription signé'
        ], $response);
    }


    #[Route('/admin/inscription/reject/{licence}', name: 'admin_registration_reject', methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'licence')]
    public function adminRegistartionReject(
        Request $request,
        LicenceService $licenceService,
        MailerService $mailerService,
        UserDtoTransformer $userDtoTransformer,
        Licence $licence
    ): Response {
        $user = $licence->getUser();
        $fullName = $user->getIdentity()->getFullName();
        $content = 'Le dossier d\'inscription au club est incomplet ou non conforme. Merci de le transmettre à nouveau, signé, en tenant compte des modifications suivantes :';
        $response = new Response("OK", Response::HTTP_OK);
        $form = $this->createForm(LicenceRejectType::class, ['content' => $content], [
            'action' => $request->getUri(),
            'attr' => ['data-action' => 'turbo:submit-end->modal#handleFormSubmit']
        ]);
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted()) {
            if ($form->isValid()) {
                $subject = 'Votre inscription au club de Vtt Évasion Ludres';
                $data = $form->getData();

                $result = $mailerService->sendMailToMember($userDtoTransformer->fromEntity($licence->getUser()), $subject, $data['content']);
                $tansition = ($licence->getState()->isYearly()) ? 'reject_yearly_file' : 'reject_trial_file';
                if ($result['success'] && $licenceService->applyTransition($licence, $tansition)) {
                    $this->entityManager->persist($licence);
                    $this->entityManager->flush();
                    $this->addFlash('success', "Le message a bien été envoyé");
                } else {
                    $this->addFlash('danger', "Une erreur est survenue");
                }
                return $this->redirectToRoute('admin_registrations', [
                    'filtered' => true,
                    'p' => $request->query->get('p'),
                ]);
            }
            $response = new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->render('licence/admin/reject.modal.html.twig', [
            'form' => $form->createView(),
            'licence' => $licence,
            'fullname' => $fullName,
        ], $response);
    }

    #[Route('/admin/inscription/register/{licence}', name: 'admin_registration_register', methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'licence')]
    public function adminRegistartionRegister(
        Request $request,
        ValidateLicence $validateLicence,
        Licence $licence
    ): Response {
        $user = $licence->getUser();
        $fullName = $user->getIdentity()->getFullName();
        $response = new Response("OK", Response::HTTP_OK);
        $data = [
            'licenceNumber' => $user->getLicenceNumber(),
            'medicalCertificateDate' => $user->getHealth()->getMedicalCertificateDate(),
        ];
        $form = $this->createForm(LicenceRegisterType::class, $data, [
            'is_yearly' => $licence->getState()->isYearly(),
            'licences' => $user->getLicences(),
            'attr' => ['data-action' => 'turbo:submit-end->modal#handleFormSubmit']
        ]);
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted()) {
            if ($form->isValid()) {
                $validateLicence->execute($request, $licence);

                $this->addFlash('success', "Le dossier de {$fullName} a bien été inscrit à la fédération");

                return $this->redirectToRoute('admin_registrations', [
                    'filtered' => true,
                    'p' => $request->query->get('p'),
                ]);
            }
            $response = new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->render('licence/admin/register.modal.html.twig', [
            'form' => $form->createView(),
            'licence' => $licence,
            'fullname' => $fullName,
        ], $response);
    }
}
