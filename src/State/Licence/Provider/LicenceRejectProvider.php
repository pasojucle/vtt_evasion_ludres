<?php

declare(strict_types=1);

namespace App\State\Licence\Provider;

use App\Dto\Enum\DialogType;
use App\Dto\Payload\LicenceReject;
use App\Dto\View\DialogModalView;
use App\Entity\Licence;
use App\State\Interface\FormComponentProviderInterface;
use App\State\Message\Provider\MessageProvider;

/**
 * @implements FormComponentProviderInterface<LicenceReject>
 */
class LicenceRejectProvider implements FormComponentProviderInterface
{
    public function __construct(
        private MessageProvider $messageProvider,
    ) {
    }

    public function mapToView(object $licenceRegister): DialogModalView
    {
        $licence = $licenceRegister->licence;
        return new DialogModalView(
            type: DialogType::WARNING,
            title: sprintf('Inscription de %s', $licence->getMember()->getIdentity()->getFullName()),
            action: 'Envoyer',
            message: '<p>Ce message sera envoyé automatiquement à l\'adhérent, à la validation du formulaire. Précisez clairement les informations manquantes ou erronées.</p>',
            icon: 'lucide:send'
        );
    }

    public function createContextObject(Licence $licence): LicenceReject
    {
        return new LicenceReject(
            $licence,
            $this->messageProvider->getMessageById('REGISTRATION_REJECT_MESSAGE', $licence->getMember())
        );
    }
}
