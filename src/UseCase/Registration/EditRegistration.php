<?php

declare(strict_types=1);

namespace App\UseCase\Registration;

use App\Entity\Identity;
use App\Entity\Licence;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\IdentityRepository;
use App\Repository\UserRepository;
use App\Security\LoginAuthenticator;
use App\Service\CommuneService;
use App\Service\IdentityService;
use App\Service\LicenceService;
use App\Service\MailerService;
use App\Service\ParameterService;
use App\Service\SeasonService;
use App\Service\StringService;
use App\Service\UploadService;
use App\Validator\UniqueMember;
use App\ViewModel\UserPresenter;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\SecurityEvents;
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
        private RequestStack $requestStack,
        private LoginAuthenticator $authenticator,
        private UrlGeneratorInterface $urlGenerator,
        private IdentityService $identityService,
        private SeasonService $seasonService,
        private ParameterService $parameterService,
        private MailerService $mailerService,
        private ValidatorInterface $validator,
        private EventDispatcherInterface $dispatcher,
        private TokenStorageInterface $tokenStorage,
        private LicenceService $licenceService,
        private StringService $stringService,
        private CommuneService $communeService
    ) {
    }

    public function execute(Request $request, FormInterface $form, array $progress): void
    {
        $season = $progress['season'];
        $user = $form->getData();
        $manualAuthenticating = false;
        $session = $request->getSession();

        if ($form->get('plainPassword') && $form->get('plainPassword')->getData()) {
            $identity = $this->registerNewUser($user, $form);
            $manualAuthenticating = true;
            $this->uniqueMemberValidator($form, $identity);
            if ($form->isValid()) { 
                $session->set('sendLoginRegistration', true);
            }
        }

        if($user->getMemberIdentity()->getBirthCommune()) {
            $this->communeService->addIfNotExists($user->getMemberIdentity()->getBirthCommune());
        };

        if (null !== $user->getMemberIdentity()->getBirthDate()) {
            $category = $this->licenceService->getCategory($user);
            $user->getSeasonLicence($season)->setCategory($category);
            if (Licence::CATEGORY_MINOR === $category) {
                $this->schoolTestingRegistrationValidator($form, $progress);
                $this->identityService->setAddress($user);
            }
        }
        $this->UploadFile($request, $user);

        $category = $user->getSeasonLicence($season)->getCategory();
        if (true === $session->get('sendLoginRegistration') && 
            ((Licence::CATEGORY_MINOR === $category && $user->getKinshipIdentity()) || Licence::CATEGORY_ADULT === $category )) {
                $this->sendMail($user);
                $session->set('sendLoginRegistration', false);
        }

        $isMedicalCertificateRequired = $this->isMedicalCertificateRequired($progress);
        $user->getSeasonLicence($season)->setMedicalCertificateRequired($isMedicalCertificateRequired);

        if ($form->isValid()) {
            if (UserType::FORM_OVERVIEW === $progress['current']->form) {
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
        $fullName = $this->stringService->clean(strtoupper($identity->getName()) . ucfirst($identity->getFirstName()));
        $user->setLicenceNumber(substr(preg_replace('/[^a-zA-Z0-9]+/', '', $fullName), 0, 20) . $nextId);

        return $identity;
    }

    private function uniqueMemberValidator(FormInterface &$form, Identity $identity): void
    {
        $values = [
            'name' => $identity->getName(),
            'firstName' => $identity->getFirstName(),
        ];
        $violations = $this->validator->validate($values, [new UniqueMember()]);

        if (!empty((string) $violations)) {
            $form->addError(new FormError((string) $violations));
        }
    }

    private function schoolTestingRegistrationValidator(FormInterface &$form, array $progress)
    {
        $schoolTestingRegistration = $this->parameterService->getSchoolTestingRegistration($progress['user']);
        if (!$schoolTestingRegistration['value'] && !$progress['user']->seasonLicence->isFinal) {
            $form->addError(new FormError($schoolTestingRegistration['message']));
        }
    }

    private function sendMail(User $user): void
    {
        $this->userPresenter->present($user);
        $user = $this->userPresenter->viewModel();
        $email = $user->mainEmail;
        $this->mailerService->sendMailToMember([
            'name' => $user->member->name,
            'firstName' => $user->member->firstName,
            'email' => $email,
            'subject' => 'Création de compte sur le site VTT Evasion Ludres',
            'licenceNumber' => $user->licenceNumber,
        ], 'EMAIL_REGISTRATION');
    }

    private function isMedicalCertificateRequired(array $progress): bool
    {
        $isMedicalCertificateRequired = false;
        $user = $progress['user'];
        if (Licence::TYPE_RIDE !== $user->seasonLicence->type) {
            $medicalCertificateDate = $user->health->medicalCertificateDateObject;
            $medicalCertificateDuration = (Licence::TYPE_HIKE === $user->seasonLicence->type) ? 5 : 3;
            $intervalDuration = new DateInterval('P' . $medicalCertificateDuration . 'Y');
            $today = new DateTime();
            if (null === $medicalCertificateDate || $medicalCertificateDate < $today->sub($intervalDuration) || $user->health->isMedicalCertificateRequired) {
                $isMedicalCertificateRequired = true;
            }
        }

        return $isMedicalCertificateRequired;
    }

    private function UploadFile(Request $request, User $user): void
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
            'registration' => $this->urlGenerator->generate('registration_file', [
                'user' => $user->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);
    }

    private function authenticating(Request $request, User $user): void
    {
        $token = new UsernamePasswordToken($user, $user->getPassword(), $user->getRoles());
        $this->tokenStorage->setToken($token);

        // $event = new SecurityEvents($request);
        $event = new SecurityEvents();
        $this->dispatcher->dispatch($event, SecurityEvents::INTERACTIVE_LOGIN);
    }
}
