<?php

declare(strict_types=1);

namespace App\UseCase\Registration;

use App\Entity\Licence;
use App\Entity\RegistrationStep;
use App\Repository\RegistrationStepRepository;

class GetRegistrationByTypes
{
    public function __construct(private RegistrationStepRepository $registrationStepRepository)
    {
    }

    public function execute(): array
    {
        $licenceTypes = [
            'essai' => false,
            'final' => true,
        ];
        $renders = [RegistrationStep::RENDER_VIEW, RegistrationStep::RENDER_FILE];
        $registrationByTypes = [];
        $labels = [];
        foreach (array_keys(Licence::CATEGORIES) as $category) {
            $labels['categories'][] = Licence::CATEGORIES[$category];
            foreach ($licenceTypes as $licenceTypeLabel => $isYearly) {
                $labels['licenceTypeLabels'][] = $licenceTypeLabel;
                foreach ($renders as $render) {
                    $labels['render'][$category][$isYearly][] = (RegistrationStep::RENDER_VIEW === $render)
                        ? '<i class="fas fa-desktop"></i>'
                        : '<i class="fas fa-file-pdf"></i>';
                    $registrationByTypes[$category][$isYearly][$render] = $this->registrationStepRepository->findByCategoryAndFinal($category, $isYearly, $render);
                }
            }
        }

        return [$labels, $registrationByTypes];
    }
}
