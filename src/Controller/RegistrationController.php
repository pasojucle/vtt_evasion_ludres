<?php

namespace App\Controller;

use DateTime;
use App\Form\UserType;
use App\Entity\Licence;
use App\Service\PdfService;
use App\Service\LicenceService;
use App\DataTransferObject\User;
use App\Entity\RegistrationStep;
use App\Entity\User as UserEntity;
use App\Form\RegistrationStepType;
use App\Service\RegistrationService;
use App\Security\LoginFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\MembershipFeeRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\RegistrationStepRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationController extends AbstractController
{
    public const OUT_PDF = 1;
    public const OUT_SCREEN = 2;
    
    private RegistrationStepRepository $registrationStepRepository;
    private EntityManagerInterface $entityManager;
    private SessionInterface $session;

    public function __construct(RegistrationStepRepository $registrationStepRepository, EntityManagerInterface $entityManager, SessionInterface $session)
    {
        $this->registrationStepRepository = $registrationStepRepository;
        $this->entityManager = $entityManager;
        $this->session = $session;
    }

    /**
     * @Route("/inscription/info", name="registration_detail")
     */
    public function registrationDetail(): Response
    {
        return $this->render('registration/detail.html.twig');
    }

    /**
     * @Route("/inscription", name="registration_member")
     */
    public function registration(): Response
    {
        $this->session->remove('registrationMaxStep');
        
        return $this->render('registration/member.html.twig');
    }


    /**
     * @Route("/inscription/{step}", name="registration_form")
     * @Route("/mon_compte/inscription/{step}", name="user_registration_form")
     */
    public function registerForm(
        Request $request,
        registrationService $registrationService,
        UserPasswordEncoderInterface $passwordEncoder,
        LoginFormAuthenticator $authenticator,
        GuardAuthenticatorHandler $guardHandler,
        SluggerInterface $slugger,
        MembershipFeeRepository $membershipFeeRepository,
        int $step
    ): Response
    {
        if ((int) $this->session->get('registrationMaxStep') < $step) {
            $this->session->set('registrationMaxStep', $step);
        }
        if (!$step) {
            $this->session->remove('healthQuestions');
        }
        $progress = $registrationService->getProgress($step);
        $form = $progress['form'];
        if (null !== $progress['user'] && 1 === $step) {
            $this->session->set('registrationMaxStep',  $progress['max_step'] );
        }
        if (null !== $form) {
            $form->handleRequest($request);
        }

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $route = (4 > $step) ? 'registration_form': 'user_registration_form';
            $user = $form->getData();
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
            }

            if (null !== $user->getIdentities()->first()->getBirthDate()) {
                $today = new DateTime();
                $age = $today->diff($user->getIdentities()->first()->getBirthDate());
                $category =  (18 > (int) $age->format('%y')) ? Licence::CATEGORY_MINOR : Licence::CATEGORY_ADULT;
                $season = $registrationService->getSeason();
                $user->getSeasonLicence($season)->setCategory($category);
                if (Licence::CATEGORY_MINOR === $category) {
                    $identityKinShip = $user->getIdentities()->last();
                    $addressKinShip = $identityKinShip->getAddress();
                    if (!$identityKinShip->hasAddress() && null !== $addressKinShip) {
                        $identityKinShip->setAddress(null);
                        $this->entityManager->remove($addressKinShip);
                    }
                }
            }

            if ($request->files->get('user')) {
                $pictureFile = $request->files->get('user')['identities'][0]['pictureFile'];
                if ($pictureFile) {
                    $originalFilename = pathinfo($pictureFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename.'-'.uniqid().'.'.$pictureFile->guessExtension();
                    if (!is_dir($this->getParameter('uploads_directory'))) {
                        mkdir($this->getParameter('uploads_directory'));
                    }
                    try {
                        $pictureFile->move(
                            $this->getParameter('uploads_directory'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        // ... handle exception if something happens during file upload
                    }
                    $user->getIdentities()->first()->setPicture($newFilename);
                }
            }

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->session->remove('registrationPath');
            if ($manualAuthenticating) {
                $this->session->set('registrationPath', $route);
                // after validating the user and saving them to the database
                // authenticate the user and use onAuthenticationSuccess on the authenticator
                $guardHandler->authenticateUserAndHandleSuccess(
                    $user,          // the User object you just created
                    $request,
                    $authenticator, // authenticator whose onAuthenticationSuccess you want to use
                    'main'          // the name of your firewall in security.yaml
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
     * @Route("/inscription/file/{user}", name="registration_file")
     */
    public function registrationFile(
        LicenceService $licenceService,
        MembershipFeeRepository $membershipFeeRepository,
        PdfService $pdfService,
        UserEntity $user
    ): Response
    {
        $season = $licenceService->getCurrentSeason();
        $seasonLicence = $user->getSeasonLicence($season);
        $category = $seasonLicence->getCategory();
        $steps = $this->registrationStepRepository->findByCategoryAndTesting($category, $seasonLicence->isTesting());
        $allmembershipFee = $membershipFeeRepository->findAll();

        $files = [];
        if (!empty($steps)) {
            foreach($steps as $key => $step) {
                $isKinship = false;
                if ($steps[$key]->getForm() === UserType::FORM_IDENTITY && null !== $steps[$key + 1]) {
                    $isKinship = true;
                }

                if ($step->isToPdf()) {
                    if (null !== $step->getFilename()) {
                        $filename = './files/'.$step->getFilename();
                        
                        $files[] = ['filename' => $filename, 'form' => $step->getForm()];
                    }
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
        
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
