<?php

namespace App\Controller;

use DateTime;
use DateInterval;
use App\Form\UserType;
use App\Entity\Licence;
use App\Service\PdfService;
use App\Service\UserService;
use App\Service\MailerService;
use App\Service\UploadService;
use App\Service\LicenceService;
use App\Entity\RegistrationStep;
use App\Entity\RegistrationStepGroup;
use App\Service\ParameterService;
use App\Entity\User as UserEntity;
use App\Form\RegistrationStepType;
use App\Service\RegistrationService;
use App\Repository\ContentRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\MembershipFeeRepository;
use App\UseCase\RegistrationStep\GetReplaces;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\RegistrationStepRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Repository\RegistrationStepGroupRepository;
use App\Service\OrderByService;
use App\UseCase\Registration\EditRegistration;
use App\UseCase\Registration\GetProgress;
use App\UseCase\RegistrationStep\EditRegistrationStep;
use App\ViewModel\RegistrationStepPresenter;
use App\ViewModel\UserPresenter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RegistrationController extends AbstractController
{
    public function __construct(
        private RegistrationStepRepository $registrationStepRepository,
        private RegistrationStepGroupRepository $registrationStepGroupRepository,
        private EntityManagerInterface $entityManager,
        private RequestStack $requestStack,
        private LicenceService $licenceService,
        private MailerService $mailerService,
        private UserService $userService,
        private UploadService $uploadService, 
        private GetReplaces $getReplaces,
        private OrderByService $orderByService,
        private RegistrationService $registrationService,
        private GetProgress $getProgress
    )
    {
    }

    /**
     * @Route("/inscription/info", name="registration_detail")
     */
    public function registrationDetail(
        ContentRepository $contentRepository
    ): Response
    {
        return $this->render('registration/detail.html.twig', [
            'content' => $contentRepository->findOneByRoute('registration_detail'),
        ]);
    }

    /**
     * @Route("/inscription/taris", name="registration_membership_fee")
     */
    public function registrationMemberShipFee(
        MembershipFeeRepository $membershipFeeRepository
    ): Response
    {
        return $this->render('registration/membership_fee_page.html.twig', [
            'all_membership_fee' => $membershipFeeRepository->findAll(),
        ]);
    }

    /**
     * @Route("/inscription", name="registration_form", defaults={"step"=1})
     * @Route("/mon-compte/inscription/{step}", name="user_registration_form")
     */
    public function registerForm(
        Request $request,
        MembershipFeeRepository $membershipFeeRepository,
        ParameterService $parameterService,
        EditRegistration $editRegistration,
        int $step
    ): Response
    {
        if ((int) $this->requestStack->getSession()->get('registrationMaxStep') < $step) {
            $this->requestStack->getSession()->set('registrationMaxStep', $step);
        }

        $progress = $this->getProgress->execute($step);
        if (Licence::STATUS_IN_PROCESSING < $progress['seasonLicence']->getStatus() && $progress['current']->form !== UserType::FORM_REGISTRATION_FILE) {
            return $this->redirectToRoute('registration_download', ['user' => $progress['user']->getId()]);
        }
        $form = $progress['current']->formObject;

        $schoolTestingRegistration = $parameterService->getSchoolTestingRegistration($progress['user']);
        if (!$schoolTestingRegistration['value'] && $progress['current']->form === UserType::FORM_MEMBER && !$progress['user']->getId()) {
            $this->addFlash('success', $schoolTestingRegistration['message']);
        }
        $maxStep = $step;
        $this->requestStack->getSession()->set('registrationMaxStep',  $maxStep);

        if (null !== $form) {
            $form->handleRequest($request);
        } 

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $editRegistration->execute($request, $form, $progress);
            if ($form->isValid()) {
                return $this->redirectToRoute('user_registration_form', ['step' => $progress['nextIndex']]);
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
            'season_licence' => $progress['seasonLicence'],
            'maxStep' => $this->requestStack->getSession()->get('registrationMaxStep'),
            'all_membership_fee' => $membershipFeeRepository->findAll(),
            'user' => $progress['user'],
            'media' => RegistrationStep::RENDER_VIEW,
        ]);
    }

    /**
     * @Route("/admin/param_inscription/", name="admin_registration_steps")
     */
    public function adminRegistrationSteps(
        Request $request
    ): Response
    {
        $isFinalValues = ['essai' => false, 'final' =>true];
        $renders = [RegistrationStep::RENDER_VIEW, RegistrationStep::RENDER_FILE];
        $registrationByTypes = [];
        $labels = [];
        foreach (array_keys(Licence::CATEGORIES) as $category) {
            $labels['categories'][] = Licence::CATEGORIES[$category];
            foreach ($isFinalValues as $isFinalLabel => $isFinal) {
                $labels['isFinalLabels'][] = $isFinalLabel;
                foreach($renders as $render) {
                    $labels['render'][$category][$isFinal][] = (RegistrationStep::RENDER_VIEW === $render)
                        ? '<i class="fas fa-desktop"></i>'
                        : '<i class="fas fa-file-pdf"></i>';
                    $registrationByTypes[$category][$isFinal][$render] = $this->registrationStepRepository->findByCategoryAndFinal($category, $isFinal, $render);
                }
            }
        }

        return $this->render('registration/admin/registrationList.html.twig', [
            'registrationStepGroups' => $this->registrationStepGroupRepository->findAll(),
            'registrationByTypes' => $registrationByTypes,
            'labels' => $labels,
        ]);
    }

    /**
     * @Route("/admin/registrationStepGroup/ordonner/{group}", name="admin_registration_step_group_order", options={"expose"=true},)
     */
    public function adminregistrationStepGroupOrder(
        Request $request,
        RegistrationStepGroup $group
    ): Response
    {
        $newOrder = $request->request->get('newOrder');
        $regitrationStepGroups = $this->registrationStepGroupRepository->findAll();

        $this->orderByService->setNewOrders($group, $regitrationStepGroups, $newOrder);

        return $this->redirectToRoute('admin_registration_steps');
    }


    /**
     * @Route("/admin/registrationStep/ordonner/{step}", name="admin_registration_step_order", options={"expose"=true},)
     */
    public function adminregistrationStepOrder(
        Request $request,
        RegistrationStep $step
    ): Response
    {
        $newOrder = $request->request->get('newOrder');
        $regitrationSteps = $this->registrationStepRepository->findByGroup($step->getRegistrationStepGroup());

        $this->orderByService->setNewOrders($step, $regitrationSteps, $newOrder);

        return $this->redirectToRoute('admin_registration_steps');
    }

    /**
     * @Route("/admin/param_inscription/{step}", name="admin_registration_step")
     */
    public function adminRegistrationStep(
        Request $request,
        EditRegistrationStep $editRegistrationStep,
        RegistrationStep $step
    ): Response
    {
        $form = $this->createForm(RegistrationStepType::class, $step);

        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $editRegistrationStep->execute($request, $form);
        }

        return $this->render('registration/admin/registrationStep.html.twig', [
            'registrationStep' => $step,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/inscription/telechargement/{user}", name="registration_download")
     */
    public function registrationDownload(
        UserEntity $user
    ): Response
    {
        $season = $this->licenceService->getCurrentSeason();
        
        return $this->render('registration/download.html.twig', [
            'user_entity' => $user,
            'licence' => $user->getSeasonLicence($season),
        ]);
    }

    /**
     * @Route("/inscription/file/{user}", name="registration_file")
     */
    public function registrationFile(
        MembershipFeeRepository $membershipFeeRepository,
        PdfService $pdfService,
        UserPresenter $presenter,
        RegistrationStepPresenter $registrationStepPresenter,
        UserEntity $user
    ): Response
    {
        $season = $this->licenceService->getCurrentSeason();
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
            foreach($steps as $key => $step) {
                $registrationStepPresenter->present($step, $presenter->viewModel(), 1, RegistrationStep::RENDER_FILE);
                $step = $registrationStepPresenter->viewModel();
                if (null !== $step->filename) {
                    $filename = './files/'.$step->filename;
                    $files[] = ['filename' => $filename, 'form' => $step->form];
                }
                if (in_array($step->form, $registrationDocumentForms)) {
                    $registrationDocumentSteps[$step->form] = $step->content;
                } elseif (null !== $step->content) {
                    $html = null;
                    if (null !== $step->form) {
                        $form = $step->formObject;
                        $html = $this->renderView('registration/registrationPdf.html.twig', [
                            'user' => $presenter->viewModel(),
                            'all_membership_fee' => $allmembershipFee,
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
                        $files[] = ['filename' => $pdfFilepath, 'form' => $step->form];
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
            array_unshift($files, ['filename' => $pdfFilepath, 'form' => null]);
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
