<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Entity\Health;
use App\Entity\Address;
use App\Entity\Licence;
use App\Entity\Approval;
use App\Entity\Identity;
use App\Form\ToolImportType;
use App\Entity\HealthQuestion;
use App\Repository\LevelRepository;
use App\Service\LicenceService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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
        UserPasswordEncoderInterface $passwordEncoder,
        LevelRepository $levelRepository
    ): Response
    {
        $form = $this->createForm(ToolImportType::class);
        $form->handleRequest($request);
        $count = null;

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            if ($request->files->get('tool_import')) {
                $userListFile = $request->files->get('tool_import')['userList'];

                $allLevel = $levelRepository->findAllTypeMember();
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
                            ->setPassword($passwordEncoder->encodePassword($user, $plainPassword))
                            ->setRoles([$role])
                            ->addApproval($approval)
                            ->addIdentity($identity)
                            ->addLicence($licence)
                            ->setHealth($health)
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
        ]);
    }
    /**
     * @Route("/admin/outil/licence/type", name="admin_update_licence_type")
     */
    public function adminUpdateLicenceType(
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder,
        LevelRepository $levelRepository
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
                    }
                    fclose($handle);
                    $this->entityManager->flush();
                }
            }
        }


        return $this->render('tool/import.html.twig', [
            'form' => $form->createView(),
            'count' => $count,
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
                        $departments[] = $number.' - '.$name;
                        
                    }
                    fclose($handle);
                    file_put_contents('../data/departments', json_encode($departments));
                }
            }
        }

        return $this->render('tool/import.html.twig', [
            'form' => $form->createView(),
            'count' => count($departments),
        ]);
    }
}