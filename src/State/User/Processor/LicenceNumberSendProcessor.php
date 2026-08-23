<?php

declare(strict_types=1);

namespace App\State\User\Processor;

use App\Dto\State\TurboStreamProcessorResult;
use App\Dto\View\FlashesView;
use App\Dto\View\FlashMessageView;
use App\Entity\Member;
use App\Service\MailerService;
use App\Service\MessageService;
use App\Service\ReplaceKeywordsService;

class LicenceNumberSendProcessor
{
    public function __construct(
        private MailerService $mailerService,
        private MessageService $messageService,
        private ReplaceKeywordsService $replaceKeywords,
    ) {
    }

    public function process(Member $entity): TurboStreamProcessorResult
    {
        $identity = $entity->getMainIdentity();
        $subject = 'Votre numero de licence';
        $message = $this->messageService->getMessageById('EMAIL_LICENCE_VALIDATE');

        $this-> mailerService->sendMailToMember(
            $identity->getEmail(),
            $identity->getFullName(),
            $subject,
            $this->replaceKeywords->replaceUserData($message, $entity)
        );

        return new TurboStreamProcessorResult(
            success: true,
            flashMessages: FlashesView::create('success', 'Le messsage a été envoyé avec succès.')
        );
    }
}
