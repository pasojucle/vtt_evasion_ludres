<?php

declare(strict_types=1);

namespace App\State\User\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\TurboStreamProcessorInterface;
use App\Core\Dto\ActionDirectPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\TurboStreamProcessorResult;
use App\Entity\Member;
use App\Service\MailerService;
use App\Service\MessageService;
use App\Service\ReplaceKeywordsService;

/**
 * @implements TurboStreamProcessorInterface<ActionDirectPayload>
 */
class LicenceNumberSendProcessor implements TurboStreamProcessorInterface
{
    public function __construct(
        private MailerService $mailerService,
        private MessageService $messageService,
        private ReplaceKeywordsService $replaceKeywords,
    ) {
    }

    public function process(PayloadInterface $payload, ?HandlerContext $context = null): TurboStreamProcessorResult
    {
        /**
         * @var Member $member
         */
        $member = $payload->data;
        $identity = $member->getMainIdentity();
        $subject = 'Votre numero de licence';
        $message = $this->messageService->getMessageById('EMAIL_LICENCE_VALIDATE');

        $this-> mailerService->sendMailToMember(
            $identity->getEmail(),
            $identity->getFullName(),
            $subject,
            $this->replaceKeywords->replaceUserData($message, $member)
        );

        return new TurboStreamProcessorResult(
            success: true,
            messageKey: 'Le messsage a été envoyé avec succès.',
            data: $member,
        );
    }
}
