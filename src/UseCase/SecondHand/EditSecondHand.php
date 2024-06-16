<?php

declare(strict_types=1);

namespace App\UseCase\SecondHand;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\SecondHand;
use App\Entity\User;
use App\Repository\SecondHandRepository;
use App\Service\MailerService;
use App\Service\UploadService;
use DateTimeImmutable;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EditSecondHand
{
    public function __construct(
        private UserDtoTransformer $userDtoTransformer,
        private MailerService $mailerService,
        private Security $security,
        private UploadService $uploadService,
        private SecondHandRepository $secondHandRepository,
        private UrlGeneratorInterface $urlGenerator
    ) {
    }
    public function execute(FormInterface $form, Request $request): void
    {
        $secondHand = $form->getData();
        /** @var User $user */
        $user = $this->security->getUser();
        $this->setData($secondHand, $user);
        $this->saveFile($secondHand, $request);
        $this->secondHandRepository->save($secondHand, true);
        $this->sendEmail($secondHand, $user);
    }

    private function setData(SecondHand $secondHand, User $user): void
    {
        $secondHand->setUser($user)
            ->setCreatedAt(new DateTimeImmutable())
            ->setDeleted(false)
            ->setDisabled(false)
            ->setValidedAt(null)
        ;
    }

    private function saveFile(SecondHand $secondHand, Request $request): void
    {
        $files = $request->files->all('second_hand');
        if (array_key_exists('filename', $files) && $file = $files['filename']) {
            $secondHand->setFilename($this->uploadService->uploadFile($file, 'second_hands_directory_path'));
        }
    }

    private function sendEmail(SecondHand $secondHand, User $user): void
    {
        $userDto = $this->userDtoTransformer->fromEntity($user);
        $this->mailerService->sendMailToClub([
            'name' => $userDto->member->name,
            'firstName' => $userDto->member->firstName,
            'email' => $userDto->member->email,
            'subject' => 'Nouvelle Annonce d\'occasion sur le site VTT Evasion Ludres',
            'secondHand' => $this->urlGenerator->generate('admin_second_hand_show', [
                'secondHand' => $secondHand->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);
    }
}
