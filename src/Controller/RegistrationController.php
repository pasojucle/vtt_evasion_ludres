<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Licence;
use App\Entity\RegistrationStep;
use App\Entity\User as UserEntity;
use App\Form\UserType;
use App\Repository\ContentRepository;
use App\Repository\MembershipFeeRepository;
use App\Repository\RegistrationStepGroupRepository;
use App\Repository\RegistrationStepRepository;
use App\Service\MailerService;
use App\Service\OrderByService;
use App\Service\ParameterService;
use App\Service\PdfService;
use App\Service\RegistrationService;
use App\Service\SeasonService;
use App\Service\UploadService;
use App\Service\UserService;
use App\UseCase\Registration\EditRegistration;
use App\UseCase\Registration\GetProgress;
use App\UseCase\RegistrationStep\GetReplaces;
use App\ViewModel\RegistrationStepPresenter;
use App\ViewModel\UserPresenter;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    public function __construct(
        private RegistrationStepRepository $registrationStepRepository,
        private RegistrationStepGroupRepository $registrationStepGroupRepository,
        private EntityManagerInterface $entityManager,
        private RequestStack $requestStack,
        private SeasonService $seasonService,
        private MailerService $mailerService,
        private UserService $userService,
        private UploadService $uploadService,
        private GetReplaces $getReplaces,
        private OrderByService $orderByService,
        private RegistrationService $registrationService,
        private GetProgress $getProgress
    ) {
    }

    #[Route('/inscription/info', name: 'registration_detail', methods: ['GET'])]
    public function registrationDetail(
        ContentRepository $contentRepository
    ): Response {
        return $this->render('registration/detail.html.twig', [
            'content' => $contentRepository->findOneByRoute('registration_detail'),
        ]);
    }

    #[Route('/inscription/tarifs', name: 'registration_membership_fee', methods: ['GET'])]
    public function registrationMemberShipFee(
        MembershipFeeRepository $membershipFeeRepository,
        ContentRepository $contentRepository
    ): Response {
        return $this->render('registration/membership_fee_page.html.twig', [
            'all_membership_fee' => $membershipFeeRepository->findAll(),
            'content' => $contentRepository->findOneByRoute('registration_membership_fee'),
        ]);
    }

    #[Route('/inscription"', name: 'registration_form', methods: ['GET', 'POST'], defaults:['step' => 1])]
    #[Route('/mon-compte/inscription/{step}', name: 'user_registration_form', methods: ['GET', 'POST'])]
    public function registerForm(
        Request $request,
        MembershipFeeRepository $membershipFeeRepository,
        ParameterService $parameterService,
        EditRegistration $editRegistration,
        int $step
    ): Response {
        if ((int) $this->requestStack->getSession()->get('registrationMaxStep') < $step) {
            $this->requestStack->getSession()->set('registrationMaxStep', $step);
        }

        $progress = $this->getProgress->execute($step);
        $user = $progress['user'];
        if (Licence::STATUS_IN_PROCESSING < $user->seasonLicence->status && UserType::FORM_REGISTRATION_FILE !== $progress['current']->form) {
            return $this->redirectToRoute('registration_download', [
                'user' => $user->entity->getId(),
            ]);
        }
        $form = $progress['current']->formObject;

        $schoolTestingRegistration = $parameterService->getSchoolTestingRegistration($progress['user']);
        if (!$schoolTestingRegistration['value'] && UserType::FORM_MEMBER === $progress['current']->form && !$progress['user']->licenceNumber) {
            $this->addFlash('success', $schoolTestingRegistration['message']);
        }
        $maxStep = $step;
        $this->requestStack->getSession()->set('registrationMaxStep', $maxStep);

        if (null !== $form) {
            $form->handleRequest($request);
        }

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $editRegistration->execute($request, $form, $progress);
            if ($form->isValid()) {
                return $this->redirectToRoute('user_registration_form', [
                    'step' => $progress['nextIndex'],
                ]);
            }
        }

        return $this->render('registration/registrationForm.html.twig', [
            'step' => $step,
            'steps' => $progress['steps'],
            'form' => (null !== $form) ? $form->createView() : null,
            'template' => $progress['current']->template,
            'prev' => $progress['prevIndex'],
            'current' => $progress['current'],
            'next' => $progress['nextIndex'],
            'maxStep' => $this->requestStack->getSession()->get('registrationMaxStep'),
            'all_membership_fee' => $membershipFeeRepository->findAll(),
            'user' => $progress['user'],
            'media' => RegistrationStep::RENDER_VIEW,
        ]);
    }

    #[Route('/inscription/telechargement/{user}', name: 'registration_download', methods: ['GET'])]
    public function registrationDownload(
        UserEntity $user
    ): Response {
        $season = $this->seasonService->getCurrentSeason();

        return $this->render('registration/download.html.twig', [
            'user_entity' => $user,
            'licence' => $user->getSeasonLicence($season),
        ]);
    }

    #[Route('/inscription/file/{user}', name: 'registration_file', methods: ['GET'])]
    public function registrationFile(
        MembershipFeeRepository $membershipFeeRepository,
        ContentRepository $contentRepository,
        PdfService $pdfService,
        UserPresenter $presenter,
        RegistrationStepPresenter $registrationStepPresenter,
        UserEntity $user
    ): Response {
        $season = $this->seasonService->getCurrentSeason();
        $seasonLicence = $user->getSeasonLicence($season);
        $category = $seasonLicence->getCategory();
        $steps = $this->registrationStepRepository->findByCategoryAndFinal($category, $seasonLicence->isFinal(), RegistrationStep::RENDER_FILE);

        $allmembershipFee = $membershipFeeRepository->findAll();
        if ($this->getUser() === $user) {
            $today = new DateTime();
            $seasonLicence->setCreatedAt($today);
        }
        $presenter->present($user);
        $files = [];
        $registrationDocumentSteps = [];

        $registrationDocumentForms = [
            UserType::FORM_REGISTRATION_DOCUMENT,
            UserType::FORM_MEMBER,
            UserType::FORM_KINSHIP,
            UserType::FORM_HEALTH,
            UserType::FORM_APPROVAL,
        ];
        if (!empty($steps)) {
            foreach ($steps as $key => $step) {
                $registrationStepPresenter->present($step, $presenter->viewModel(), 1, RegistrationStep::RENDER_FILE);
                $step = $registrationStepPresenter->viewModel();
                if (null !== $step->filename) {
                    $filename = './files/'.$step->filename;
                    $files[] = [
                        'filename' => $filename,
                        'form' => $step->form,
                    ];
                }
                if (in_array($step->form, $registrationDocumentForms, true)) {
                    $registrationDocumentSteps[$step->form] = $step->content;
                } elseif (null !== $step->content) {
                    $html = null;
                    if (null !== $step->form) {
                        $form = $step->formObject;
                        $html = $this->renderView('registration/registrationPdf.html.twig', [
                            'user' => $presenter->viewModel(),
                            'all_membership_fee' => $allmembershipFee,
                            'membership_fee_content' => $contentRepository->findOneByRoute('registration_membership_fee')?->getContent(),
                            'current' => $step,
                            'form' => $form->createView(),
                            'media' => RegistrationStep::RENDER_FILE,
                            'template' => $this->registrationService->getTemplate($step->form),
                        ]);
                    } else {
                        $html = $step->content;
                    }

                    if (null !== $html) {
                        $pdfFilepath = $pdfService->makePdf($html, $step->title);
                        $files[] = [
                            'filename' => $pdfFilepath,
                            'form' => $step->form,
                        ];
                    }
                }
            }
        }
        if (!empty($registrationDocumentSteps)) {
            $registration = $this->renderView('registration/registrationPdf.html.twig', [
                'user' => $presenter->viewModel(),
                'user_entity' => $user,
                'registration_document_steps' => $registrationDocumentSteps,
                'category' => $seasonLicence->getCategory(),
                'licence' => $presenter->viewModel()->seasonLicence,
                'media' => RegistrationStep::RENDER_FILE,
            ]);
            $pdfFilepath = $pdfService->makePdf($registration, 'registration_temp');
            array_unshift($files, [
                'filename' => $pdfFilepath,
                'form' => null,
            ]);
        }

        $filename = $pdfService->joinPdf($files, $user);

        $fileContent = file_get_contents($filename);

        $response = new Response($fileContent);
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            'inscription_vtt_evasion_ludres.pdf'
        );

        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', 'application/pdf');

        return $response;
    }
}
