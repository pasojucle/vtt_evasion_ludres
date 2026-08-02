<?php

declare(strict_types=1);

namespace App\State\User\Processor;

use App\Dto\State\TurboStreamProcessorResult;
use App\Dto\View\FlashMessageView;
use App\Entity\User;
use App\Service\MailerService;
use App\Service\MessageService;


class LicenceNumberSendProcessor
{
    public function __construct(
        private MailerService $mailerService,
        private MessageService $messageService,
    ){}

    public function process(User $entity): TurboStreamProcessorResult
    {
        $identity = $entity->getMainIdentity();
        $subject = 'Votre numero de licence';
        $this-> mailerService->sendMailToMember(
            $identity->getEmail(),
            $identity->getFullName(),
            $subject,
            $this->messageService->getMessageById('EMAIL_LICENCE_VALIDATE')
        );

        return new TurboStreamProcessorResult(
            'flash_messages/flash_messages.lazy.html.twig',
            new FlashMessageView('success', 'Le messsage a été envoyé avec succès')
        );
    }
}
