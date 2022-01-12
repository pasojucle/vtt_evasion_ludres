<?php

namespace App\UseCase\Registration;

use App\Entity\Identity;
use DateTime;
use DateInterval;
use App\Form\UserType;
use App\Entity\Licence;
use App\Entity\User;
use App\Service\UploadService;
use App\ViewModel\UserPresenter;
use App\Repository\UserRepository;
use Symfony\Component\Form\FormError;
use App\Repository\IdentityRepository;
use App\Security\LoginFormAuthenticator;
use App\Service\IdentityService;
use App\Service\LicenceService;
use App\Service\MailerService;
use App\Service\ParameterService;
use App\Validator\UniqueMember;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EditRegistration
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UploadService $uploadService,
        private UserRepository $userRepository,
        private IdentityRepository $identityRepository,
        private UserPresenter $userPresenter,
        private UserPasswordHasherInterface $passwordHasher,
        private GuardAuthenticatorHandler $guardHandler,
        private RequestStack $requestStack,
        private LoginFormAuthenticator $authenticator,
        private UrlGeneratorInterface $urlGenerator,
        private IdentityService $identityService,
        private LicenceService $licenceService,
        private ParameterService $parameterService,
        private MailerService $mailerService,
        private ValidatorInterface $validator
    )
    {
    }

    public function execute(Request $request, FormInterface $form, array $progress): void
    {
        $season = $progress['season'];
        $user = $form->getData();
        $manualAuthenticating = false;

        if ($form->get('plainPassword') && $form->get('plainPassword')->getData()) {
            $identity = $this->registerNewUser($user, $form);
            $manualAuthenticating = true;
            $this->uniqueMemberValidator($form, $identity);
            if ($form->isValid()) {
                $this->sendMail($user);
            }
        }

        if (null !== $user->getIdentities()->first()->getBirthDate()) {
            $category = $this->licenceService->getCategory($user);
            $user->getSeasonLicence($season)->setCategory($category);
            if (Licence::CATEGORY_MINOR === $category) {
                $this->schoolTestingRegistrationValidator($form, $progress);
                $this->identityService->setAddress($user);
            }
        }
        $this->UploadFile($request, $user);

        $isMedicalCertificateRequired = $this->isMedicalCertificateRequired($progress);
        $user->getSeasonLicence($season)->setMedicalCertificateRequired($isMedicalCertificateRequired);

        if ($form->isValid()) {
            if ($progress['current']->form === UserType::FORM_OVERVIEW) {
                $user->getSeasonLicence($season)->setStatus(Licence::STATUS_WAITING_VALIDATE);
                $this->sendMailToClub($user);
            }
            $this->entityManager->flush();

            $this->requestStack->getSession()->remove('registrationPath');
            if ($manualAuthenticating) {
                $this->authenticating($request, $user);
            }
        }
    }

    private function registerNewUser(User $user, FormInterface $form): Identity
    {
        $user->setPassword(
            $this->passwordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            )
        );

        $nextId = $this->userRepository->findNextId();
        $identity = $user->getFirstIdentity();
        $fullName = strtoupper($identity->getName()).ucfirst($identity->getFirstName());
        $user->setLicenceNumber(substr($fullName, 0, 20).$nextId);
        return $identity;
    }

    private function uniqueMemberValidator(FormInterface &$form, Identity $identity): void
    {
        $values = ['name' => $identity->getName(), 'firstName' => $identity->getFirstName()];
        $violations = $this->validator->validate($values, [new UniqueMember()]);

        if (!empty((string) $violations)) {
            $form->addError(new FormError((string) $violations));
        }
    }

    private function schoolTestingRegistrationValidator(FormInterface &$form, array $progress)
    {
        $schoolTestingRegistration = $this->parameterService->getSchoolTestingRegistration($progress['user']);
                if (!$schoolTestingRegistration['value'] && !$progress['seasonLicence']->isFinal()) {
                    $form->addError(new FormError($schoolTestingRegistration['message']));
                }
    }

    private function sendMail(User $user):void
    {
        $this->userPresenter->present($user);
        $user = $this->userPresenter->viewModel();
        $email = $user->getContactEmail();
        $this->mailerService->sendMailToMember([
            'name' => $user->member->name,
            'firstName' => $user->member->firstName,
            'email' => $email,
            'subject' => 'Création de compte sur le site VTT Evasion Ludres',
            'licenceNumber' => $user->licenceNumber,], 'EMAIL_REGISTRATION');  
    }

    private function isMedicalCertificateRequired(array $progress): bool
    {
        $isMedicalCertificateRequired = false;
        $seasonLicence = $progress['seasonLicence'];
        $user = $progress['user']->entity;
        if ($seasonLicence->getType() !== Licence::TYPE_RIDE) {
            $medicalCertificateDate = $user->getHealth()->getMedicalCertificateDate();
            $medicalCertificateDuration = ($seasonLicence->getType() === Licence::TYPE_HIKE) ? 5 : 3;
            $intervalDuration = new DateInterval('P'.$medicalCertificateDuration.'Y');
            $today = new DateTime();
            if (null === $medicalCertificateDate || $medicalCertificateDate < $today->sub($intervalDuration) || $user->getHealth()->isMedicalCertificateRequired()) {
                $isMedicalCertificateRequired = true;
            }
        }
        return $isMedicalCertificateRequired;
    }

    Private function UploadFile(Request $request, User $user): void
    {
        $requestFile = $request->files->get('user');
        if (null !== $requestFile && array_key_exists('identities', $requestFile) && null !== $requestFile['identities'][0]['pictureFile']) {
            $pictureFile = $requestFile['identities'][0]['pictureFile'];
            $newFilename = $this->uploadService->uploadFile($pictureFile);
            if (null !== $newFilename) {
                $user->getIdentities()->first()->setPicture($newFilename);
            }
        }
    }

    private function sendMailToClub(User $user): void
    {
        $identity = $user->getFirstIdentity();
        $this->mailerService->sendMailToClub([
            'name' => $identity->getName(),
            'firstName' => $identity->getFirstName(),
            'email' => $identity->getEmail(),
            'subject' => 'Nouvelle Inscription sur le site VTT Evasion Ludres',
            'registration' => $this->urlGenerator->generate('registration_file', ['user' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);
    }

    private function authenticating(Request $request, User $user):void
    {
        $this->requestStack->getSession()->set('registrationPath', 'user_registration_form');
        $this->guardHandler->authenticateUserAndHandleSuccess(
            $user,
            $request,
            $this->authenticator,
            'main'
        );
    }
}