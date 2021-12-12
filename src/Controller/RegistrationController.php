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
use App\Service\IdentityService;
use App\Service\ParameterService;
use App\Entity\User as UserEntity;
use App\Form\RegistrationStepType;
use App\Repository\UserRepository;
use App\Service\RegistrationService;
use App\Repository\ContentRepository;
use Symfony\Component\Form\FormError;
use App\Repository\IdentityRepository;
use App\Security\LoginFormAuthenticator;
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
use App\UseCase\RegistrationStep\EditRegistrationStep;
use App\ViewModel\UserPresenter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{
    public const OUT_PDF = 1;
    public const OUT_SCREEN = 2;

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
        private RegistrationService $registrationService
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
     * @Route("/inscription/{step}", name="registration_form")
     * @Route("/mon-compte/inscription/{step}", name="user_registration_form")
     */
    public function registerForm(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        LoginFormAuthenticator $authenticator,
        GuardAuthenticatorHandler $guardHandler,
        UserRepository $userRepository,
        MembershipFeeRepository $membershipFeeRepository,
        IdentityRepository $identityRepository,
        UserService $userService,
        ParameterService $parameterService,
        IdentityService $identityService,
        int $step
    ): Response
    {
        if ((int) $this->requestStack->getSession()->get('registrationMaxStep') < $step) {
            $this->requestStack->getSession()->set('registrationMaxStep', $step);
        }

        $progress = $this->registrationService->getProgress($step);
        if (Licence::STATUS_IN_PROCESSING < $progress['seasonLicence']->getStatus() && $progress['current']->getForm() !== UserType::FORM_REGISTRATION_FILE) {
            return $this->redirectToRoute('registration_download', ['user' => $progress['user']->getId()]);
        }
        $form = $progress['form'];
        $season = $this->registrationService->getSeason();
        $schoolTestingRegistration = $parameterService->getParameterByName('SCHOOL_TESTING_REGISTRATION');
        $schoolTestingRegistrationMessage = 'L\'inscription à l\'école vtt est close pour la saison '.$season;
        if (1 === $step) {
            if (!$schoolTestingRegistration && !$progress['user']->getId()) {
                $this->addFlash('success', $schoolTestingRegistrationMessage);
            }
            $maxStep = $step;
            $this->requestStack->getSession()->set('registrationMaxStep',  $maxStep);
        }
        if (null !== $form) {
            $form->handleRequest($request);
        }

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $route = (4 > $step) ? 'registration_form': 'user_registration_form';
            $user = $form->getData();
            $manualAuthenticating = false;
            
            $minLimit = new DateTime();
            $minLimit->sub(new DateInterval('P80Y'));
            $maxLimit = new DateTime();
            $maxLimit->sub(new DateInterval('P5Y'));

            if (!$user->getIdentities()->isEmpty()) {
                foreach($user->getIdentities() as $identity) {
                    if (null !== $identity->getBirthDate() && !($minLimit < $identity->getBirthDate() && $identity->getBirthDate() < $maxLimit)) {
                        $form->addError(new FormError('La date de naissance est invalide'));
                    }

                    if ($identity->isEmpty()) {
                        $address = $identity->getAddress();
                        if (null !== $address) {
                            $identity->setAddress(null);
                            $this->entityManager->remove($address);
                        }
                        $user->removeIdentity($identity);
                        $this->entityManager->remove($identity);
                    }
                }
            }

            if ($form->get('plainPassword') && $form->get('plainPassword')->getData()) {
                // encode the plain password
                $user->setPassword(
                    $passwordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
                $manualAuthenticating = true;

                $nextId = $userRepository->findNextId();

                $identity = $user->getFirstIdentity();
                $fullName = strtoupper($identity->getName()).ucfirst($identity->getFirstName());
                $user->setLicenceNumber(substr($fullName, 0, 20).$nextId);

                $userSameName = $identityRepository->findByNameAndFirstName($identity->getName(), $identity->getFirstName());
                if (!empty($userSameName)) {
                    $form->addError(new FormError('Un compte avec le nom '.$identity->getName().' '.$identity->getFirstName().' existe déjà'));
                }
                if ($form->isValid()) {
                    $domainUser = $userService->convertToUser($user);
                    $email = $domainUser->getContactEmail();
                    $this->mailerService->sendMailToMember([
                        'name' => $identity->getName(),
                        'firstName' => $identity->getFirstName(),
                        'email' => $email,
                        'subject' => 'Création de compte sur le site VTT Evasion Ludres',
                        'licenceNumber' => $user->getLicenceNumber(),], 'EMAIL_REGISTRATION');
                }
            }

            if (null !== $user->getIdentities()->first()->getBirthDate()) {
                $category = $this->licenceService->getCategory($user);
                $user->getSeasonLicence($season)->setCategory($category);
                if (Licence::CATEGORY_MINOR === $category) {
                    if (!$schoolTestingRegistration && !$user->getSeasonLicence($season)->isFinal()) {
                        $form->addError(new FormError($schoolTestingRegistrationMessage));
                    }
                    $identityService->setAddress($user);
                }
            }
            $requestFile = $request->files->get('user');
            if (null !== $requestFile && array_key_exists('identities', $requestFile) && null !== $requestFile['identities'][0]['pictureFile']) {
                $pictureFile = $requestFile['identities'][0]['pictureFile'];
                $newFilename = $this->uploadService->uploadFile($pictureFile);
                if (null !== $newFilename) {
                    $user->getIdentities()->first()->setPicture($newFilename);
                }
            }

            $isMedicalCertificateRequired = false;
            if ($user->getSeasonLicence($season)->getType() !== Licence::TYPE_RIDE) {
                $medicalCertificateDate = $user->getHealth()->getMedicalCertificateDate();
                $medicalCertificateDuration = ($user->getSeasonLicence($season)->getType() === Licence::TYPE_HIKE) ? 5 : 3;
                $intervalDuration = new DateInterval('P'.$medicalCertificateDuration.'Y');
                $today = new DateTime();
                if (null === $medicalCertificateDate || $medicalCertificateDate < $today->sub($intervalDuration) || $user->getHealth()->isMedicalCertificateRequired()) {
                    $isMedicalCertificateRequired = true;
                }
            }
            $user->getSeasonLicence($season)->setMedicalCertificateRequired($isMedicalCertificateRequired);

            if ($form->isValid()) {
                if ($progress['current']->getForm() === UserType::FORM_OVERVIEW) {

                    $user->getSeasonLicence($season)->setStatus(Licence::STATUS_WAITING_VALIDATE);

                    $identity = $user->getFirstIdentity();
                    $this->mailerService->sendMailToClub([
                        'name' => $identity->getName(),
                        'firstName' => $identity->getFirstName(),
                        'email' => $identity->getEmail(),
                        'subject' => 'Nouvelle Inscription sur le site VTT Evasion Ludres',
                        'registration' => $this->generateUrl('registration_file', ['user' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                    ]);
                }
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $this->requestStack->getSession()->remove('registrationPath');
                if ($manualAuthenticating) {
                    $this->requestStack->getSession()->set('registrationPath', $route);
                    $guardHandler->authenticateUserAndHandleSuccess(
                        $user,
                        $request,
                        $authenticator,
                        'main'
                    );
                }

                return $this->redirectToRoute($route, ['step' => $progress['next']]);
            }
        }

        return $this->render('registration/registrationForm.html.twig', [
            'step' => $step,
            'steps' => $progress['steps'],
            'form' => (null!== $form) ? $form->createView() : null,
            'prev' => $progress['prev'],
            'current' => $progress['current'],
            'next' => $progress['next'],
            'user_entity' => $progress['user'],
            'season_licence' => $progress['seasonLicence'],
            'maxStep' => $this->requestStack->getSession()->get('registrationMaxStep'),
            'all_membership_fee' => $membershipFeeRepository->findAll(),
            'user' => $this->userService->convertToUser($progress['user']),
            'media' => self::OUT_SCREEN,
            'replaces' => $this->getReplaces->execute($progress['current'], $progress['user'], $progress['form']),
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
        $registrationPageSteps = [];

        $registrationPageForms = [
            UserType::FORM_MEMBER,
            UserType::FORM_KINSHIP,
            UserType::FORM_HEALTH,
            UserType::FORM_APPROVAL,
        ];
        if (!empty($steps)) {
            foreach($steps as $key => $step) {


                if (null !== $step->getForm()) {
                    $formName = str_replace('form.', '', UserType::FORMS[$step->getForm()]);
                }

                if (null !== $step->getFilename()) {
                    $filename = './files/'.$step->getFilename();
                    $files[] = ['filename' => $filename, 'form' => $step->getForm()];
                }
                if (in_array($step->getForm(), $registrationPageForms)) {
                    $registrationPageSteps[$step->getForm()] =  $step->getContent();
                } elseif (null !== $step->getContent()) {
                    $isKinship = false;
                    if ($steps[$key]->getForm() === UserType::FORM_IDENTITY && null !== $steps[$key + 1]) {
                        $isKinship = true;
                    }
                    
                    $html = null;
                    if (null !== $step->getForm()) {
                        $form = $this->createForm(UserType::class, $user, [
                            'attr' =>[
                                'action' => $this->generateUrl('registration_form', ['step' => $step->getId()]),
                            ],
                            'current' => $step,
                            'is_kinship' => $isKinship,
                            'category' => $category,
                            'season_licence' => $seasonLicence,
                        ]);

                        $template = 'registration/form/'.$formName.'.html.twig';

                        $pages = preg_split('#{{ saut_page }}#', $step->getContent());
                        if (1 < count($pages)) {
                            $content = '';
                            foreach($pages as $page) {
                                $content .= '<div class="page_break">'.$page.'</div>';
                            }
                            $step->setContent($content);
                        }
                        
                        $html = $this->renderView('registration/registrationPdf.html.twig', [
                            'user' => $presenter->viewModel(),
                            'all_membership_fee' => $allmembershipFee,
                            'current' => $step,
                            'form' => $form->createView(),
                            'media' => self::OUT_PDF,
                            'template' => $template,
                            'replaces' => $this->getReplaces->execute($step, $user, $form),
                        ]);
                    } else {
                        $html = $step->getContent();
                    }

                    if (null !== $html) {
                        $pdfFilepath = $pdfService->makePdf($html, $step->getTitle());
                        $files[] = ['filename' => $pdfFilepath, 'form' => $step->getForm()];
                    }
                }
            }
        }
        if (!empty($registrationPageSteps)) {
            $registration = $this->renderView('registration/registrationPdf.html.twig', [
                'user' => $presenter->viewModel(),
                'user_entity' => $user,
                'registration_page_steps' => $registrationPageSteps,
                'category' => $seasonLicence->getCategory(),
                'licence' => $presenter->viewModel()->getSeasonLicence(),
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
