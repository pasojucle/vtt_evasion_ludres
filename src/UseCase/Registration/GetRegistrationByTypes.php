<?php

declare(strict_types=1);

namespace App\UseCase\Registration;

use App\Entity\Enum\LicenceCategoryEnum;
use App\Entity\RegistrationStep;
use App\Repository\RegistrationStepRepository;
use Symfony\Contracts\Translation\TranslatorInterface;

class GetRegistrationByTypes
{
    public function __construct(
        private RegistrationStepRepository $registrationStepRepository,
        private TranslatorInterface $translator,
    ) {
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
        foreach ([LicenceCategoryEnum::SCHOOL, LicenceCategoryEnum::ADULT] as $category) {
            $labels['categories'][] = $category->trans($this->translator);
            foreach ($licenceTypes as $licenceTypeLabel => $isYearly) {
                $labels['licenceTypeLabels'][] = $licenceTypeLabel;
                foreach ($renders as $render) {
                    $labels['render'][$category->value][$isYearly][] = (RegistrationStep::RENDER_VIEW === $render)
                        ? '<i class="fas fa-desktop"></i>'
                        : '<i class="fas fa-file-pdf"></i>';
                    $registrationByTypes[$category->value][$isYearly][$render] = $this->registrationStepRepository->findByCategoryAndFinal($category, $isYearly, $render);
                }
            }
        }

        return [$labels, $registrationByTypes];
    }
}
