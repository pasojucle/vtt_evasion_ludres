<?php

namespace App\Controller;

use DateTime;
use DateInterval;
use App\Form\UserType;
use App\Entity\Licence;
use App\Service\PdfService;
use App\Service\UserService;
use App\Service\LicenceService;
use App\DataTransferObject\User;
use App\Entity\RegistrationStep;
use App\Entity\User as UserEntity;
use App\Form\RegistrationStepType;
use App\Repository\ContentRepository;
use App\Service\RegistrationService;
use App\Security\LoginFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\MembershipFeeRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\RegistrationStepRepository;
use App\Repository\UserRepository;
use App\Service\MailerService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationController extends AbstractController
{
    public const OUT_PDF = 1;
    public const OUT_SCREEN = 2;
    
    private RegistrationStepRepository $registrationStepRepository;
    private EntityManagerInterface $entityManager;
    private SessionInterface $session;
    private MailerService $mailerService;
    private LicenceService $licenceService;

    public function __construct(
        RegistrationStepRepository $registrationStepRepository,
        EntityManagerInterface $entityManager,
        SessionInterface $session,
        LicenceService $licenceService,
        MailerService $mailerService
    )
    {
        $this->registrationStepRepository = $registrationStepRepository;
        $this->entityManager = $entityManager;
        $this->session = $session;
        $this->mailerService = $mailerService;
        $this->licenceService = $licenceService;
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
     * @Route("/inscription/{step}", name="registration_form")
     * @Route("/mon-compte/inscription/{step}", name="user_registration_form")
     */
    public function registerForm(
        Request $request,
        RegistrationService $registrationService,
        UserPasswordEncoderInterface $passwordEncoder,
        LoginFormAuthenticator $authenticator,
        GuardAuthenticatorHandler $guardHandler,
        UserRepository $userRepository,
        UserService $userService,
        MembershipFeeRepository $membershipFeeRepository,
        int $step
    ): Response
    {
        if ((int) $this->session->get('registrationMaxStep') < $step) {
            $this->session->set('registrationMaxStep', $step);
        }

        $progress = $registrationService->getProgress($step);

        if ($progress['seasonLicence']->isValid() || $progress['seasonLicence']->isDownload()) {
            return $this->redirectToRoute('registration_download', ['user' => $progress['user']->getId()]);
        }
        $form = $progress['form'];

        if (1 === $step) {
            $maxStep = $step;
            if (null !== $progress['seasonLicence'] && null !== $progress['seasonLicence']->getType()) {
                $maxStep = $progress['max_step'];
            } 
            $this->session->set('registrationMaxStep',  $maxStep);
        }
        if (null !== $form) {
            $form->handleRequest($request);
        }

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $route = (4 > $step) ? 'registration_form': 'user_registration_form';
            $user = $form->getData();
            $season = $registrationService->getSeason();
            $manualAuthenticating = false;
            if ($form->get('plainPassword') && $form->get('plainPassword')->getData()) {
                // encode the plain password
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
                $manualAuthenticating = true;

                $nextId = $userRepository->findNextId();

                $identity = $user->getFirstIdentity();
                $fullName = strtoupper($identity->getName()).ucfirst($identity->getFirstName());
                $user->setLicenceNumber(substr($fullName, 0, 20).$nextId);

                $this->mailerService->sendMailToMember([
                    'name' => $identity->getName(),
                    'firstName' => $identity->getFirstName(),
                    'email' => $identity->getEmail(),
                    'subject' => 'Création de compte sur le site VTT Evasion Ludres',
                    'licenceNumber' => $user->getLicenceNumber(),]);
            }

            if (null !== $user->getIdentities()->first()->getBirthDate()) {
                $category = $this->licenceService->getCategory($user);
                $user->getSeasonLicence($season)->setCategory($category);
                if (Licence::CATEGORY_MINOR === $category) {
                    foreach($user->getIdentities() as $identity) {
                        
                        if (null !== $identity->getKinShip()) {
                            $addressKinShip = $identity->getAddress();
                            if (!$identity->hasAddress() && null !== $addressKinShip) {
                                $identity->setAddress(null);
                                $this->entityManager->remove($addressKinShip);
                            }
                        }
                    }
                }
            }

            if ($request->files->get('user')) {
                $pictureFile = $request->files->get('user')['identities'][0]['pictureFile'];
                $newFilename = $userService->uploadFile($pictureFile);
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

            if (!$user->getIdentities()->isEmpty()) {
                foreach($user->getIdentities() as $identity) {
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

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->session->remove('registrationPath');
            if ($manualAuthenticating) {
                $this->session->set('registrationPath', $route);
                $guardHandler->authenticateUserAndHandleSuccess(
                    $user,
                    $request,
                    $authenticator,
                    'main'
                );
            }
            return $this->redirectToRoute($route, ['step' => $progress['next']]);
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
            'maxStep' => $this->session->get('registrationMaxStep'),
            'all_membership_fee' => $membershipFeeRepository->findAll(),
            'user' => new User($progress['user']),
            'media' => self::OUT_SCREEN,
        ]);
    }

    /**
     * @Route("/admin/inscription/", name="admin_registration_steps")
     */
    public function adminRegistrationSteps(
        Request $request
    ): Response
    {
        return $this->render('registration/admin/registrationTypes.html.twig', [
            'registrationSteps' => $this->registrationStepRepository->findAll(),
        ]);
    }

    /**
     * @Route("/admin/inscription/{step}", name="admin_registration_step")
     */
    public function adminRegistrationStep(
        Request $request,
        RegistrationStep $step
    ): Response
    {
        $form = $this->createForm(RegistrationStepType::class, $step);

        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $step = $form->getData();

            $this->entityManager->persist($step);
            $this->entityManager->flush();
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
        UserEntity $user
    ): Response
    {
        $season = $this->licenceService->getCurrentSeason();
        $seasonLicence = $user->getSeasonLicence($season);
        $category = $seasonLicence->getCategory();
        $steps = $this->registrationStepRepository->findByCategoryAndFinal($category, $seasonLicence->isFinal(), RegistrationStep::RENDER_FILE);
        $allmembershipFee = $membershipFeeRepository->findAll();

        $files = [];
        if (!empty($steps)) {
            foreach($steps as $key => $step) {
                $isKinship = false;
                if ($steps[$key]->getForm() === UserType::FORM_IDENTITY && null !== $steps[$key + 1]) {
                    $isKinship = true;
                }
                if (null !== $step->getFilename()) {
                    $filename = './files/'.$step->getFilename();
                    
                    $files[] = ['filename' => $filename, 'form' => $step->getForm()];
                }
                if ($step->isToPdf()) {
                    $html = null;
                    if (null !== $step->getContent()) {
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
                            $formName = str_replace('form.', '', UserType::FORMS[$step->getForm()]);

                            $template = 'registration/'.$formName.'.html.twig';

                            $html = $this->renderView('registration/registrationPdf.html.twig', [
                                'user' => new User($user),
                                'all_membership_fee' => $allmembershipFee,
                                'current' => $step,
                                'form' => $form->createView(),
                                'media' => self::OUT_PDF,
                                'template' => $template,
                            ]);
                        } else {
                            $html = $step->getContent();
                        }
                    }
                    if (null !== $html) {
                        $pdfFilepath = $pdfService->makePdf($html, $step->getTitle());
                        $files[] = ['filename' => $pdfFilepath, 'form' => $step->getForm()];
                    }
                }
            }
        }
        $registration = $this->renderView('registration/registrationPdf.html.twig', [
            'user' => new User($user),
            'user_entity' => $user,
        ]);
        $pdfFilepath = $pdfService->makePdf($registration, 'registration_temp');
        $files[] = ['filename' => $pdfFilepath, 'form' => $step->getForm()];

        $filename = $pdfService->joinPdf($files, $user);

        $fileContent = file_get_contents($filename);

        $response = new Response($fileContent);
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            'inscription_vtt_evasion_ludres.pdf'
        );

        $seasonLicence->setIsDownload(true);
        $this->entityManager->flush();
        
        $response->headers->set('Content-Disposition', $disposition);

        if ($this->getUser() === $user) {
            $identity = $user->getFirstIdentity();
            $this->mailerService->sendMailToClub([
                'name' => $identity->getName(),
                'firstName' => $identity->getFirstName(),
                'email' => $identity->getEmail(),
                'subject' => 'Nouvelle Inscription sur le site VTT Evasion Ludres',
                'registration' => $this->generateUrl('registration_file', ['user' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            ]);
        }

        return $response;
    }
}
