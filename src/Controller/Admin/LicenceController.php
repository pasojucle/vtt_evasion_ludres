<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractCrudController;
use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Dto\Form\LicenceRegister;
use App\Entity\Licence;
use App\Form\Admin\LicenceRegisterType;
use App\Form\Admin\LicenceRejectType;
use App\Service\LicenceService;
use App\Service\MailerService;
use App\State\Licence\Processor\LicenceDeleteProcessor;
use App\State\Licence\Processor\LicenceReceiveProcessor;
use App\State\Licence\Processor\LicenceRegisterProcessor;
use App\State\Licence\Provider\LicenceDeleteProvider;
use App\State\Licence\Provider\LicenceReceiveProvider;
use App\State\Licence\Provider\LicenceRegisterProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class LicenceController extends AbstractCrudController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Route('/admin/inscription/delete/{licence}', name: 'admin_delete_licence', methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'licence')]
    public function adminDeleteLicence(
        Request $request,
        LicenceDeleteProcessor $processor,
        LicenceDeleteProvider $provider,
        Licence $licence
    ): Response {
        $response = new Response("OK", Response::HTTP_OK);
        $form = $this->createForm(FormType::class, null, [
            'action' => $request->getUri(),
            'attr' => ['data-action' => 'turbo:submit-end->modal#handleFormSubmit']
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted()) {
            if ($form->isValid()) {
                $this->addFlash('success', $processor->process($licence));

                return $this->redirectToRoute('admin_registration_list', [
                    'filtered' => true,
                    'p' => $request->query->get('p'),
                ]);
            }
            $response = new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->render('components/_dialog.modal.html.twig', [
            'form' => $form->createView(),
            'dialog' => $provider->mapToView($licence),
        ], $response);
    }

    #[Route('/admin/inscription/receive/{licence}', name: 'admin_registration_receive', methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'licence')]
    public function adminRegistartionReceive(
        Request $request,
        LicenceReceiveProvider $provider,
        LicenceReceiveProcessor $processor,
        Licence $licence
    ): Response {
        return $this->handleDialogAction($request, $licence, $provider, $processor);
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
        $user = $licence->getMember();
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

                $result = $mailerService->sendMailToMember($userDtoTransformer->fromEntity($licence->getMember()), $subject, $data['content']);
                $tansition = ($licence->getState()->isYearly()) ? 'reject_yearly_file' : 'reject_trial_file';
                if ($result['success'] && $licenceService->applyTransition($licence, $tansition)) {
                    $this->entityManager->persist($licence);
                    $this->entityManager->flush();
                    $this->addFlash('success', "Le message a bien été envoyé");
                } else {
                    $this->addFlash('danger', "Une erreur est survenue");
                }
                return $this->redirectToRoute('admin_registration_list', [
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
        LicenceRegisterProvider $provider,
        LicenceRegisterProcessor $processor,
        Licence $licence
    ): Response {
        $member = $licence->getMember();

        return $this->handleDialogAction(
            request: $request,
            object: new LicenceRegister(
                $licence,
                $member->getLicenceNumber(),
                $member->getHealth()->getMedicalCertificateDate()
            ),
            provider: $provider,
            processor: $processor,
            formClass: LicenceRegisterType::class,
            formOptions: [ 
                'disabled_licence_number' => count($member->getLicences()) > 1 || !$licence->getState()->isYearly(),
            ]
        );
    }
}
