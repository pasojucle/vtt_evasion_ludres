<?php

declare(strict_types=1);

namespace App\State\Licence\Provider;

use App\Dto\Enum\DialogType;
use App\Dto\Payload\LicenceRegister;
use App\Dto\View\DialogModalView;
use App\Entity\Licence;
use App\State\Interface\FormComponentProviderInterface;

/**
 * @implements FormComponentProviderInterface<LicenceRegister>
 */
class LicenceRegisterProvider implements FormComponentProviderInterface
{
    public function mapToView(object $entity, ?string $fallback = null): DialogModalView
    {
        $licence = $entity->licence;
        return new DialogModalView(
            type: DialogType::SUCCESS,
            title: sprintf('Inscription de %s', $licence->getMember()->getIdentity()->getFullName()),
            action: 'Inscrit',
            message: 'Enregistrement de l\'inscription auprès de la FFVélo.',
            icon: 'lucide:check-check'
        );
    }

    public function createContextObject(Licence $licence): LicenceRegister
    {
        $member = $licence->getMember();

        return new LicenceRegister(
            $licence,
            $member->getLicenceNumber(),
            $member->getHealth()->getMedicalCertificateDate()
        );
    }

    public function getFormOptions(Licence $licence): array
    {
        $member = $licence->getMember();

        return [
            'disabled_licence_number' => count($member->getLicences()) > 1 || !$licence->getState()->isYearly(),
        ];
    }
}
