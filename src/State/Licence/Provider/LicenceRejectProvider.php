<?php

declare(strict_types=1);

namespace App\State\Licence\Provider;

use App\Core\Contract\Provider\DataInitializerInterface;
use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\Enum\DialogType;
use App\Dto\Payload\LicenceReject;
use App\Dto\View\DialogModalView;
use App\State\Message\Provider\MessageProvider;

/**
 * @implements FormComponentProviderInterface<LicenceReject>
 */
class LicenceRejectProvider implements FormComponentProviderInterface, DataInitializerInterface
{
    public function __construct(
        private MessageProvider $messageProvider,
    ) {
    }
    public function getView(object $data, ?HandlerContext $context = null): DialogModalView
    {
        $licence = $data->licence;
        return new DialogModalView(
            type: DialogType::WARNING,
            title: sprintf('Inscription de %s', $licence->getMember()->getIdentity()->getFullName()),
            action: 'Envoyer',
            message: '<p>Ce message sera envoyé automatiquement à l\'adhérent, à la validation du formulaire. Précisez clairement les informations manquantes ou erronées.</p>',
            icon: 'lucide:send'
        );
    }

    public function initializeData(object $data): object
    {
        return new LicenceReject(
            $data,
            $this->messageProvider->getMessageById('REGISTRATION_REJECT_MESSAGE', $data->getMember())
        );
    }

    public function getFormOptions(object $data, ?HandlerContext $context = null): array
    {
        return [
            'attr' => [
                'data-action' => 'turbo:submit-end->modal#handleFormSubmit',
            ],
        ];
    }
}
