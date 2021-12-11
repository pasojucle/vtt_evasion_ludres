<?php

namespace App\Controller\Admin;

use DateTime;
use App\Entity\User;
use App\Entity\Event;
use App\Entity\Health;
use App\Entity\Address;
use App\Entity\Licence;
use App\Entity\Session;
use App\Entity\Approval;
use App\Entity\Identity;
use App\Form\ToolImportType;
use App\Service\UserService;
use App\Entity\HealthQuestion;
use App\Service\MailerService;
use App\Service\LicenceService;
use App\Repository\UserRepository;
use App\Repository\LevelRepository;
use App\Form\Admin\LicenceNumberType;
use Symfony\Component\Form\FormError;
use App\Service\ParameterService;
use App\Service\PdfService;
use App\UseCase\Tool\GetRegistrationCertificate;
use App\ViewModel\UserPresenter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ToolController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private LicenceService $licenceService;

    public function __construct(
        EntityManagerInterface $entityManager,
        LicenceService $licenceService
    )
    {
        $this->entityManager = $entityManager;
        $this->licenceService = $licenceService;
    }
    /**
     * @Route("/admin/outil/import", name="admin_import_users")
     */
    public function adminUsers(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        LevelRepository $levelRepository
    ): Response
    {
        $form = $this->createForm(ToolImportType::class);
        $form->handleRequest($request);
        $count = null;

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            if ($request->files->get('tool_import')) {
                $userListFile = $request->files->get('tool_import')['userList'];

                $allLevel = $levelRepository->findAll();
                $levels = [];

                foreach ($allLevel as $level) {
                    $levels[$level->getId()] = $level;
                }
                $count = 0;
                if (($handle = fopen($userListFile, "r")) !== FALSE) {
                    while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        list(
                            $licenceNumber,
                            $plainPassword,
                            $genre,
                            $name,
                            $firstName,
                            $levelId,
                            $role,
                            $sexe,
                            $birthDate,
                            $age,
                            $status,
                            $licenceTypeStr,
                            $activity,
                            $createdAt,
                            $email,
                            $phone,
                            $mobile,
                            $fullAdress,
                            $rightImage,
                            $hasMedicalCetificate,
                            $medicalCetificateDate,
                        ) = $row;

                        if (preg_match('#^(N° licence ou login)$#', $licenceNumber)) {
                            continue;
                        }
                        $licenceType = Licence::TYPE_RIDE;
                        preg_match('#^([A-Za-z0-9\s-]+)\s(\d{5})\s([A-Za-z\s-]+)$#', $fullAdress, $addressData);
                        list($fullAddress, $street, $postalCode, $twown) = $addressData;
                        if (preg_match('#Rando#', $licenceTypeStr)) {
                            $licenceType = Licence::TYPE_HIKE;
                        }
                        if (preg_match('#Sport#', $licenceTypeStr)) {
                            $licenceType = Licence::TYPE_SPORT;
                        }

                        $category =  ($age < 18) ? Licence::CATEGORY_MINOR : Licence::CATEGORY_ADULT;

                        $user = new User();
                        $licence = new Licence();
                        $identity = new Identity();
                        $address = new Address();
                        $health = new Health();
                        $approval = new Approval();

                        foreach (range(0, 8) as $number) {
                            $healthQuestion = new HealthQuestion();
                            $healthQuestion->setField($number);
                            $health->addHealthQuestion($healthQuestion);
                            $this->entityManager->persist($healthQuestion);
                        }
                        
                        if ($hasMedicalCetificate) {
                            $date = DateTime::createFromFormat('d/m/Y', $medicalCetificateDate);
                            $health->setMedicalCertificateDate($date);
                        }

                        $approval->setType(User::APPROVAL_RIGHT_TO_THE_IMAGE)
                            ->setValue($rightImage);
                        $licence->setCreatedAt(DateTime::createFromFormat('d/m/Y', $createdAt))
                            ->setType($licenceType)
                            ->setCategory($category)
                            ->setFinal(true)
                            ->setSeason('2021')
                            ->setStatus(Licence::STATUS_VALID);
                            ;
                        $address->setStreet($street)
                            ->setPostalCode($postalCode)
                            ->setTown($twown);
                        $identity->setFirstName($firstName)
                            ->setName($name)
                            ->setEmail($email)
                            ->setPhone(preg_replace('#\s#', '',$phone))
                            ->setMobile(preg_replace('#\s#', '',$mobile))
                            ->setBirthDate(DateTime::createFromFormat('d/m/Y', $birthDate))
                            ->setAddress($address)
                            ;

                        $user->setLicenceNumber($licenceNumber)
                            ->setActive(true)
                            ->setLevel((!empty($levelId)) ? $levels[(int) $levelId] : null)
                            ->setPassword($passwordHasher->hashPassword($user, $plainPassword))
                            ->setRoles([$role])
                            ->addApproval($approval)
                            ->addIdentity($identity)
                            ->addLicence($licence)
                            ->setHealth($health)
                            ->setPasswordMustBeChanged(true)
                            ;
                        $this->entityManager->persist($approval);
                        $this->entityManager->persist($identity);
                        $this->entityManager->persist($address);
                        $this->entityManager->persist($licence);
                        $this->entityManager->persist($health);
                        $this->entityManager->persist($user);
                        ++$count;
                    }
                    fclose($handle);
                    $this->entityManager->flush();
                }
            }
        }


        return $this->render('tool/import.html.twig', [
            'form' => $form->createView(),
            'count' => $count,
            'title' => 'Importer la liste des utilisateurs',
        ]);
    }
    /**
     * @Route("/admin/outil/licence/type", name="admin_update_licence_type")
     */
    public function adminUpdateLicenceType(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        LevelRepository $levelRepository
    ): Response
    {
        $form = $this->createForm(ToolImportType::class);
        $form->handleRequest($request);
        $count = null;
        $allLevel = $levelRepository->findAll();
        $levels = [];

        foreach ($allLevel as $level) {
            $levels[$level->getId()] = $level;
        }

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            if ($request->files->get('tool_import')) {
                $userListFile = $request->files->get('tool_import')['userList'];

                if (($handle = fopen($userListFile, "r")) !== FALSE) {
                    while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        list(
                            $licenceNumber,
                            $plainPassword,
                            $genre,
                            $name,
                            $firstName,
                            $levelId,
                            $role,
                            $sexe,
                            $birthDate,
                            $age,
                            $status,
                            $licenceTypeStr,
                            $activity,
                            $createdAt,
                            $email,
                            $phone,
                            $mobile,
                            $fullAdress,
                            $rightImage,
                            $hasMedicalCetificate,
                            $medicalCetificateDate,
                        ) = $row;

                        if (preg_match('#^(N° licence ou login)$#', $licenceNumber)) {
                            continue;
                        }
                        $licenceType = Licence::TYPE_RIDE;
                        preg_match('#^([A-Za-z0-9\s-]+)\s(\d{5})\s([A-Za-z\s-]+)$#', $fullAdress, $addressData);
                        list($fullAddress, $street, $postalCode, $twown) = $addressData;
                        if (preg_match('#Rando#', $licenceTypeStr)) {
                            $licenceType = Licence::TYPE_HIKE;
                        }
                        if (preg_match('#Sport#', $licenceTypeStr)) {
                            $licenceType = Licence::TYPE_SPORT;
                        }

                        $user = $this->entityManager->getRepository(User::class)->findOneBy(['licenceNumber' => $licenceNumber]);
                        $licence = $user->getSeasonLicence($this->licenceService->getCurrentSeason());
                        if (null !== $licence) {
                            $licence->setType($licenceType);
                            $this->entityManager->persist($user);
                        }
                        $user->setLevel((!empty($levelId)) ? $levels[(int) $levelId] : null);
                    }
                    fclose($handle);
                    $this->entityManager->flush();
                }
            }
        }


        return $this->render('tool/import.html.twig', [
            'form' => $form->createView(),
            'count' => $count,
            'title' => 'Importer la liste des utilisateurs',
        ]);
    }
    /**
     * @Route("/admin/outil/newsession/{event}", name="admin_newsession")
     */
    public function adminNewSession(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        LevelRepository $levelRepository,
        Event $event
    ): Response
    {
        $form = $this->createForm(ToolImportType::class);
        $form->handleRequest($request);
        $count = null;
        $allLevel = $levelRepository->findAll();
        $levels = [];

        foreach ($allLevel as $level) {
            $levels[$level->getId()] = $level;
        }

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            if ($request->files->get('tool_import')) {
                $userListFile = $request->files->get('tool_import')['userList'];

                if (($handle = fopen($userListFile, "r")) !== FALSE) {
                    while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        list(
                            $licenceNumber,
                            $plainPassword,
                            $genre,
                            $name,
                            $firstName,
                            $levelId,
                            $role,
                            $sexe,
                            $birthDate,
                            $age,
                            $status,
                            $licenceTypeStr,
                            $activity,
                            $createdAt,
                            $email,
                            $phone,
                            $mobile,
                            $fullAdress,
                            $rightImage,
                            $hasMedicalCetificate,
                            $medicalCetificateDate,
                        ) = $row;

                        if (preg_match('#^(N° licence ou login)$#', $licenceNumber)) {
                            continue;
                        }

                        $user = $this->entityManager->getRepository(User::class)->findOneBy(['licenceNumber' => $licenceNumber]);
                        $clustersLevelAsUser = [];
                        $availability = null;
                        foreach($event->getClusters() as $cluster) {
                            if (null !== $cluster->getLevel() && $cluster->getLevel() === $user->getLevel()) {
                                $clustersLevelAsUser[] = $cluster;
                                if (count($cluster->getMemberSessions()) <= $cluster->getMaxUsers()) {
                                    $userCluster = $cluster;
                                }
                            }
                            if (null !== $cluster->getRole() && 5 < $user->getLevel()->getId()) {
                                $userCluster = $cluster;
                                $availability = 1;
                            }
                        }

                        $userSession = new Session();
                        $userSession->setUser($user)
                            ->setCluster($userCluster)
                            ->setAvailability($availability);
                        $this->entityManager->persist($userSession);
                    }
                    fclose($handle);
                    $this->entityManager->flush();
                }
            }
        }


        return $this->render('tool/import.html.twig', [
            'form' => $form->createView(),
            'count' => $count,
            'title' => 'Envoi des types de licence',
        ]);
    }
    /**
     * @Route("/admin/outil/phone", name="admin_update_phone")
     */
    public function adminUpdatePhone(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        LevelRepository $levelRepository
    ): Response
    {
        $form = $this->createForm(ToolImportType::class);
        $form->handleRequest($request);
        $count = null;
        $allLevel = $levelRepository->findAll();
        $levels = [];

        foreach ($allLevel as $level) {
            $levels[$level->getId()] = $level;
        }

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            if ($request->files->get('tool_import')) {
                $userListFile = $request->files->get('tool_import')['userList'];

                if (($handle = fopen($userListFile, "r")) !== FALSE) {
                    while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        list(
                            $licenceNumber,
                            $plainPassword,
                            $genre,
                            $name,
                            $firstName,
                            $levelId,
                            $role,
                            $sexe,
                            $birthDate,
                            $age,
                            $status,
                            $licenceTypeStr,
                            $activity,
                            $createdAt,
                            $email,
                            $phone,
                            $mobile,
                            $fullAdress,
                            $rightImage,
                            $hasMedicalCetificate,
                            $medicalCetificateDate,
                        ) = $row;

                        if (preg_match('#^(N° licence ou login)$#', $licenceNumber)) {
                            continue;
                        }

                        $user = $this->entityManager->getRepository(User::class)->findOneBy(['licenceNumber' => $licenceNumber]);
                        $identity = $user->getFirstIdentity();
                        $identity->setPhone(preg_replace('#\s#', '',$phone))
                            ->setMobile(preg_replace('#\s#', '',$mobile))
                            ;
                        // $this->entityManager->persist($identity);
                    }
                    fclose($handle);
                    $this->entityManager->flush();
                }
            }
        }


        return $this->render('tool/import.html.twig', [
            'form' => $form->createView(),
            'count' => $count,
            'title' => 'Mise à jours des téléphones',
        ]);
    }
    /**
     * @Route("/admin/send/login", name="admin_send_login")
     */
    public function adminSendLogin(
        Request $request,
        MailerService $mailerService
    ): Response
    {
        $form = $this->createForm(ToolImportType::class);
        $form->handleRequest($request);
        $count = null;

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            if ($request->files->get('tool_import')) {
                $userListFile = $request->files->get('tool_import')['userList'];

                if (($handle = fopen($userListFile, "r")) !== FALSE) {
                    while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        list(
                            $licenceNumber,
                            $plainPassword,
                            $genre,
                            $name,
                            $firstName,
                            $levelId,
                            $role,
                            $sexe,
                            $birthDate,
                            $age,
                            $status,
                            $licenceTypeStr,
                            $activity,
                            $createdAt,
                            $email,
                            $phone,
                            $mobile,
                            $fullAdress,
                            $rightImage,
                            $hasMedicalCetificate,
                            $medicalCetificateDate,
                        ) = $row;

                        if (preg_match('#^(N° licence ou login)$#', $licenceNumber)) {
                            continue;
                        }

                        // $user = $this->entityManager->getRepository(User::class)->findOneBy(['licenceNumber' => $licenceNumber]);
                        $mailerService->sendMailToMember([
                            'name' => $name,
                            'firstName' => $firstName,
                            'email' => $email,
                            'subject' => 'Nouveau site vttevasionludres',
                            'licenceNumber' => $licenceNumber,
                            'password' => $plainPassword,
                            'sendLogin' => true,
                        ]);
                        sleep(2);
                    }
                    fclose($handle);
                    $this->entityManager->flush();
                }
            }
            $this->addFlash('success', 'Terminé');
        }

        return $this->render('tool/import.html.twig', [
            'form' => $form->createView(),
            'count' => $count,
            'title' => 'Envoi des identifiants',
        ]);
    }
    /**
     * @Route("/admin/tool/delete/user", name="admin_tool_delete_user")
     */
    public function adminDeleteUser(
        Request $request,
        UserService $userService
    ): Response
    {
        $form = $this->createForm(LicenceNumberType::class);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $licenceNumber = $data['licenceNumber'];
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['licenceNumber' => $licenceNumber]);
            if (null !== $user) {
                $fullName = $user->getFirstIdentity()->getName().' '.$user->getFirstIdentity()->getFirstName();
                $userService->deleteUser($user);
                $this->addFlash('success', "Les données de l'utilisateur $fullName ont bien été supprimées");
                return $this->redirectToRoute('admin_tool_delete_user');
            } else {
                $form->addError(new FormError("Le numéro de licence $licenceNumber n'existe pas"));
            }

        }

        return $this->render('tool/delete_user.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/admin/registration/certificate", name="admin_registration_certificate")
     */
    public function adminRegistrationCertificate(
        Request $request,
        GetRegistrationCertificate $getRegistrationCertificate,
    ): Response
    {
        $form = $this->createForm(LicenceNumberType::class);
        $form->handleRequest($request);
        $filename = null;

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $content = ($form->get('submit')->isClicked()) ? utf8_encode($data['content']) : null;
            list($filename, $content) = $getRegistrationCertificate->execute($request, $data['user'], $content);
            $form = $this->createForm(LicenceNumberType::class, ['user' => $data['user'], 'content' => $content]);
        }

        return $this->render('tool/registration_certificate.html.twig', [
            'form' => $form->createView(),
            'filename' => $filename,
        ]);
    }

    /**
     * @Route("/admin/registration/error", name="admin_registration_error")
     */
    public function adminRegistrationError(
        Request $request,
        MailerService $mailerService,
        UserPresenter $presenter,
        ParameterService $parameterService
    ): Response
    {
        $form = $this->createForm(LicenceNumberType::class);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $licence = $data['user']->getLastLicence();
            $presenter->present($data['user']);
            $user = $presenter->viewModel();
            $buttonIsCliked = $form->get('submit')->isClicked();
            $content = ($buttonIsCliked) 
                ? utf8_encode($data['content']) 
                : $parameterService->getParameterByName('EMAIL_REGISTRATION_ERROR');
            $content = str_replace('{{ licenceNumber }}', $user->getLicenceNumber(), $content);
            $form = $this->createForm(LicenceNumberType::class, ['user' => $data['user'], 'content' => $content]);
            if ($buttonIsCliked) {
                $result = $mailerService->sendMailToMember([
                    'name' => $user->getMember()['name'],
                    'firstName' => $user->getMember()['firstName'],
                    'email' => $user->getContactEmail(),
                    'subject' => 'Votre inscription au club de Vtt Évasion Ludres',
                    'licenceNumber' => $user->getLicenceNumber(),
                    'registration_error' => true,
                    'content' => $content,
                ]);
                if ($result['success']) {
                   $licence->setStatus(Licence::STATUS_IN_PROCESSING);
                    $this->entityManager->persist($licence);
                    $this->entityManager->flush(); 
                } else {
                    $form->addError(new FormError($result['message']));
                }
                
            }
        }

        return $this->render('tool/registration_error.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/outil/departements", name="admin_departments")
     */
    public function adminDepartments(
        Request $request
    ): Response
    {
        $form = $this->createForm(ToolImportType::class);
        $form->handleRequest($request);
        $departments = [];

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            if ($request->files->get('tool_import')) {
                $userListFile = $request->files->get('tool_import')['userList'];

                if (($handle = fopen($userListFile, "r")) !== FALSE) {
                    while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        list(
                            $number,
                            $name
                        ) = $row;

                        if (preg_match('#^(NUMÉRO)$#', $number)) {
                            continue;
                        }
                        $departments[$name] = $number.' - '.$name;
                        
                    }
                    fclose($handle);
                    file_put_contents('../data/departments', json_encode($departments));
                }
            }
        }

        return $this->render('tool/import.html.twig', [
            'form' => $form->createView(),
            'count' => count($departments),
            'title' => 'Liste des départements',
        ]);
    }

    /**
     * @Route("/admin/outil/export_email", name="admin_export_email")
     */
    public function adminExportEmail(
        UserRepository $userRepository
    ): Response
    {
        $users = $userRepository->findMinorAndTesting();
        $content = [];
        $row = ['Prénom', 'Nom', 'Mail', 'Date de naissance', 'Numéro de licence', 'Année', '3 séances d\'essai'];
        $content[] = implode(',', $row);

        if (!empty($users)) {
            foreach($users as $user) {
                $identity = $user->getFirstIdentity();
                $licence = $user->getLastLicence();
                $row = [$identity->getFirstName(), $identity->getName(), $identity->getEmail(), $identity->getBirthDate()->format('d/m/Y'), $user->getLicenceNumber(), $licence->getSeason(), !$licence->isFinal()];
                $content[] = implode(',', $row);
            }
        }

        $fileContent = implode(PHP_EOL, $content);

        $response = new Response($fileContent);
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            'export_email.csv'
        );
        
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}