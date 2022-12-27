<?php

declare(strict_types=1);

namespace App\UseCase\Registration;

use App\Entity\Identity;
use App\Entity\Licence;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Service\CommuneService;
use App\Service\IdentityService;
use App\Service\LicenceService;
use App\Service\MailerService;
use App\Service\StringService;
use App\Service\UploadService;
use App\ViewModel\UserPresenter;
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
        private UserPresenter $userPresenter,
        private UserPasswordHasherInterface $passwordHasher,
        private UrlGeneratorInterface $urlGenerator,
        private IdentityService $identityService,
        private MailerService $mailerService,
        private LicenceService $licenceService,
        private StringService $stringService,
        private CommuneService $communeService
    ) {
    }

    public function execute(Request $request, FormInterface $form, array $progress): void
    {
        $season = $progress['season'];
        $user = $form->getData();
        $session = $request->getSession();

        if (null !== $form->get('plainPassword') && $form->get('plainPassword')->getData()) {
            $identity = $this->registerNewUser($user, $form);
        }

        if ($user->getMemberIdentity()->getBirthCommune()) {
            $this->communeService->addIfNotExists($user->getMemberIdentity()->getBirthCommune());
        };

        if (null !== $user->getMemberIdentity()->getBirthDate()) {
            $category = $this->licenceService->getCategory($user);
            $user->getSeasonLicence($season)->setCategory($category);
            if (Licence::CATEGORY_MINOR === $category) {
                $this->identityService->setAddress($user);
            }
        }
        
        $user->getHealth()->setAtLeastOnePositveResponse();
        $session->set('health_questions', $user->getHealth()->getHealthQuestions());

        $this->UploadFile($request, $user);

        $category = $user->getSeasonLicence($season)->getCategory();

        if ($form->isValid()) {
            if (UserType::FORM_OVERVIEW === $progress['current']->form) {
                $user->getSeasonLicence($season)->setStatus(Licence::STATUS_WAITING_VALIDATE);
                $this->sendMailToClub($user);
                if (false === $user->isLoginSend()) {
                    $this->sendMailToUser($user);
                    $user->setLoginSend(true);
                }
            }
            $this->entityManager->flush();

            $session->set('registration_user_id', $user->getId());
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

    private function sendMailToUser(User $user): void
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
            'registration' => $this->urlGenerator->generate('registration_file', [
                'user' => $user->entity->getId(),
            ]),
        ], 'EMAIL_REGISTRATION');
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
}
