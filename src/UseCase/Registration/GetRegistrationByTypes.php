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
        $isFinalValues = [
            'essai' => false,
            'final' => true,
        ];
        $renders = [RegistrationStep::RENDER_VIEW, RegistrationStep::RENDER_FILE];
        $registrationByTypes = [];
        $labels = [];
        foreach (array_keys(Licence::CATEGORIES) as $category) {
            $labels['categories'][] = Licence::CATEGORIES[$category];
            foreach ($isFinalValues as $isFinalLabel => $isFinal) {
                $labels['isFinalLabels'][] = $isFinalLabel;
                foreach ($renders as $render) {
                    $labels['render'][$category][$isFinal][] = (RegistrationStep::RENDER_VIEW === $render)
                        ? '<i class="fas fa-desktop"></i>'
                        : '<i class="fas fa-file-pdf"></i>';
                    $registrationByTypes[$category][$isFinal][$render] = $this->registrationStepRepository->findByCategoryAndFinal($category, $isFinal, $render);
                }
            }
        }

        return [$labels, $registrationByTypes];
    }
}
