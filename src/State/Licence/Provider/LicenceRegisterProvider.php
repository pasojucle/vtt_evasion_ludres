<?php

declare(strict_types=1);

namespace App\State\Licence\Provider;

use App\Dto\DialogModalDto;
use App\Dto\Enum\DialogType;
use App\Dto\Form\LicenceRegister;
use App\Entity\Licence;
use App\State\DialogProviderInterface;

/**
 * @implements DialogProviderInterface<Licence>
 */
class LicenceRegisterProvider implements DialogProviderInterface
{
    public function mapToView(object $licenceRegister): DialogModalDto
    {
        assert($licenceRegister instanceof LicenceRegister);

        $licence = $licenceRegister->licence;
        return new DialogModalDto(
            type: DialogType::SUCCESS,
            title: sprintf('Inscription de %s', $licence->getMember()->getIdentity()->getFullName()),
            action: 'Inscrit',
            message: 'Enregistrement de l\'inscription auprès de la FFVélo.',
            icon: 'lucide:check-check'
        );
    }
}
