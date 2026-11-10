<?php

declare(strict_types=1);

namespace App\State\Licence\Provider;

use App\Core\Contract\Provider\DataInitializerInterface;
use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\Enum\DialogType;
use App\Dto\Payload\LicenceRegister;
use App\Dto\View\DialogModalView;

/**
 * @implements FormComponentProviderInterface<LicenceRegister>
 */
class LicenceRegisterProvider implements FormComponentProviderInterface, DataInitializerInterface
{
    public function getView(object $data, ?HandlerContext $context = null): DialogModalView
    {
        $licence = $data->licence;
        return new DialogModalView(
            type: DialogType::SUCCESS,
            title: sprintf('Inscription de %s', $licence->getMember()->getIdentity()->getFullName()),
            action: 'Inscrit',
            message: 'Enregistrement de l\'inscription auprès de la FFVélo.',
            icon: 'lucide:check-check'
        );
    }

    public function initializeData(object $data): object
    {
        $member = $data->getMember();

        return new LicenceRegister(
            $data,
            $member->getLicenceNumber(),
            $member->getHealth()->getMedicalCertificateDate()
        );
    }

    public function getFormOptions(object $data, ?HandlerContext $context = null): array
    {
        $licence = $data->licence;
        $member = $licence->getMember();

        return [
            'disabled_licence_number' => count($member->getLicences()) > 1 || !$licence->getState()->isYearly(),
            'attr' => [
                'data-action' => 'turbo:submit-end->modal#handleFormSubmit',
            ]
        ];
    }
}
