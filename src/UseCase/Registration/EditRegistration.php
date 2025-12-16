<?php

declare(strict_types=1);

namespace App\UseCase\Registration;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Dto\RegistrationProgressDto;
use App\Entity\Enum\LicenceCategoryEnum;
use App\Entity\Enum\LicenceStateEnum;
use App\Entity\Enum\RegistrationFormEnum;
use App\Entity\Identity;
use App\Entity\Licence;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\SelfAuthentication;
use App\Service\GardianService;
use App\Service\LicenceService;
use App\Service\MailerService;
use App\Service\MessageService;
use App\Service\StringService;
use App\Service\UploadService;
use App\UseCase\Registration\GetRegistrationFile;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EditRegistration
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UploadService $uploadService,
        private UserRepository $userRepository,
        private UserDtoTransformer $userDtoTransformer,
        private UserPasswordHasherInterface $passwordHasher,
        private UrlGeneratorInterface $urlGenerator,
        private GardianService $gardianService,
        private MailerService $mailerService,
        private LicenceService $licenceService,
        private StringService $stringService,
        private GetRegistrationFile $getRegistrationFile,
        private MessageService $messageService,
        private SelfAuthentication $selfAuthentication,
    ) {
    }

    public function execute(Request $request, FormInterface $form, RegistrationProgressDto $progress): array
    {
        $season = $progress->season;
        $user = $form->getData();
        $session = $request->getSession();
        $selfAuthenticating = false;
        $route = 'user_registration_form';

        if (null !== $form->get('plainPassword') && $form->get('plainPassword')->getData()) {
            $this->registerNewUser($user, $form);
            $selfAuthenticating = true;
        }

        if (null !== $user->getIdentity()->getBirthDate()) {
            $category = $this->licenceService->getCategory($user);
            $user->getSeasonLicence($season)->setCategory($category);
            if (LicenceCategoryEnum::SCHOOL === $category) {
                $this->gardianService->setAddress($user);
            }
        }

        $session->set(sprintf('health_concents_%s', $user->getLicenceNumber()), $user->getHealth()->getConsents());

        $this->UploadFile($request, $user);

        $category = $user->getSeasonLicence($season)->getCategory();

        if (RegistrationFormEnum::OVERVIEW === $progress->current->form) {
            /** @var Licence $seasonLicence */
            $seasonLicence = $user->getSeasonLicence($season);
            $registrationError = ['registration_error', ['id' => $user->getId()]];
            if (LicenceStateEnum::TRIAL_FILE_PENDING === $seasonLicence->getState()) {
                $seasonLicence->setTestingAt(new DateTimeImmutable());
                $route = 'registration_form';
                if (!$this->licenceService->applyTransition($seasonLicence, 'submit_trial_file')) {
                    return $this->error($user);
                }
            } else {
                $seasonLicence->setCreatedAt(new DateTime());
                if (!$this->licenceService->applyTransition($seasonLicence, 'submit_yearly_file')) {
                    return $this->error($user);
                }
            }
            $this->sendMailToClub($user);
            $this->sendMailToUser($user, $user->isLoginSend());
            $user->setLoginSend(true);
        }
        $this->entityManager->flush();

        if ($selfAuthenticating) {
            $this->selfAuthentication->authenticate($user);
        }

        return [$route, ['step' => $progress->nextStep]];
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
        $identity = $user->getIdentity();
        $fullName = $this->stringService->clean(strtoupper($identity->getName()) . ucfirst($identity->getFirstName()));
        $user->setLicenceNumber(substr(preg_replace('/[^a-zA-Z0-9]+/', '', $fullName), 0, 20) . $nextId);

        return $identity;
    }

    private function sendMailToUser(User $user, bool $isLoginSend): void
    {
        $userDto = $this->userDtoTransformer->identifiersFromEntity($user);

        $content = $this->messageService->getMessageByName((!$isLoginSend) ? 'EMAIL_ACCOUNT_CREATED' : 'EMAIL_REGISTRATION');
        $subject = 'Votre inscription sur le site VTT Evasion Ludres';
        $attachements = $this->getRegistrationFile->execute($user);

        $this->mailerService->sendMailToMember($userDto, $subject, $content, $attachements);
    }

    private function UploadFile(Request $request, User $user): void
    {
        $requestFile = $request->files->get('user');
        if (null !== $requestFile && array_key_exists('identity', $requestFile) && !empty($requestFile['identity']) && null !== $requestFile['identity']['pictureFile']) {
            $pictureFile = $requestFile['identity']['pictureFile'];
            $newFilename = $this->uploadService->uploadFile($pictureFile);
            if (null !== $newFilename) {
                $user->getIdentity()->setPicture($newFilename);
            }
        }
    }

    private function sendMailToClub(User $user): void
    {
        $identity = $user->getIdentity();
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

    private function error(User $user): array
    {
        $message = 'Une erreure s\'est produite pendant l\'enregistrement de l\'inscription.';
        $userDto = $this->userDtoTransformer->identifiersFromEntity($user);
        $data = [
            'subject' => 'Erreur d\'inscription',
            'message' => $message,
            'name' => $userDto->member->name,
            'firstName' => $userDto->member->firstName,
            'email' => $userDto->mainEmail,
            'user' => $userDto,
            'error' => true,
        ];
        $this->mailerService->sendMailToClub($data);

        return ['registration_error', []];
    }
}
