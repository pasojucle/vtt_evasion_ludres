<?php

declare(strict_types=1);

namespace App\State\Licence\Provider;

use App\Dto\DialogModalDto;
use App\Dto\Enum\DialogType;
use App\Dto\Form\LicenceRegister;
use App\Dto\Form\LicenceReject;
use App\Entity\Licence;
use App\State\DialogProviderInterface;
use App\State\Message\Provider\MessageProvider;

/**
 * @implements DialogProviderInterface<Licence>
 */
class LicenceRejectProvider implements DialogProviderInterface
{
    public function __construct(
        private MessageProvider $messageProvider,
    )
    { }

    public function mapToView(object $licenceRegister): DialogModalDto
    {
        assert($licenceRegister instanceof LicenceRegister);

        $licence = $licenceRegister->licence;
        return new DialogModalDto(
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
            $this->messageProvider->getMessageByName('REGISTRATION_REJECT_MESSAGE', $licence->getMember())
        ) ;
    }
}
