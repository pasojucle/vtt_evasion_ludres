<?php

declare(strict_types=1);

namespace App\UseCase\Registration;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Dto\RegistrationProgressDto;
use App\Entity\Identity;
use App\Entity\Licence;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Security\SelfAuthentication;
use App\Service\IdentityService;
use App\Service\LicenceService;
use App\Service\MailerService;
use App\Service\MessageService;
use App\Service\StringService;
use App\Service\UploadService;
use App\UseCase\Registration\GetRegistrationFile;
use DateTime;
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
        private IdentityService $identityService,
        private MailerService $mailerService,
        private LicenceService $licenceService,
        private StringService $stringService,
        private GetRegistrationFile $getRegistrationFile,
        private MessageService $messageService,
        private SelfAuthentication $selfAuthentication,
    ) {
    }

    public function execute(Request $request, FormInterface $form, RegistrationProgressDto $progress): string
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

        $this->setBithPlace($form, $user);

        if (null !== $user->getMemberIdentity()->getBirthDate()) {
            $category = $this->licenceService->getCategory($user);
            $user->getSeasonLicence($season)->setCategory($category);
            if (Licence::CATEGORY_MINOR === $category) {
                $this->identityService->setAddress($user);
            }
        }

        $session->set(sprintf('health_sworn_certifications_%s', $user->getLicenceNumber()), $user->getHealth()->getSwornCertifications());

        $this->UploadFile($request, $user);

        $category = $user->getSeasonLicence($season)->getCategory();

        if (UserType::FORM_OVERVIEW === $progress->current->form) {
            $seasonLicence = $user->getSeasonLicence($season);
            $seasonLicence->setStatus(Licence::STATUS_WAITING_VALIDATE)
                ->setCreatedAt(new DateTime());

            $this->sendMailToClub($user);
            $this->sendMailToUser($user, $user->isLoginSend());
            $user->setLoginSend(true);
            if (!$seasonLicence->isFinal()) {
                $route = 'registration_form';
            }
        }

        $this->entityManager->flush();

        $session->set('registration_user_id', $user->getId());
        if ($selfAuthenticating) {
            $this->selfAuthentication->authenticate($user);
        }

        return $route;
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

    private function setBithPlace($form, User $user): void
    {
        if ($form->has('identities')) {
            foreach ($form->get('identities') as $identity) {
                if ($identity->has('foreignBorn') && $identity->get('foreignBorn')) {
                    $user->getMemberIdentity()->setBirthCommune(null);
                } else {
                    $user->getMemberIdentity()->setBirthPlace(null);
                };
            }
        }
    }
}
