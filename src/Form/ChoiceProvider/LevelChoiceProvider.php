<?php

declare(strict_types=1);

namespace App\Form\ChoiceProvider;

use App\Entity\Enum\LevelType;
use App\Repository\LevelRepository;
use Symfony\Contracts\Translation\TranslatorInterface;

class LevelChoiceProvider
{    
    public function __construct(
        private LevelRepository $levelRepository,
        private TranslatorInterface $translator,
    ) {
    }

    public function getChoices(): array
    {
        $choices = [];

        foreach (LevelType::cases() as $levelType) {
            $choices[$levelType->trans($this->translator)] = [$this->getLabelGroup($levelType) => $levelType->value];
        }

        foreach ($this->levelRepository->findAll() as $level) {
            $choices[$level->getType()->trans($this->translator)][$level->getTitle()] = $level->getId();
        }

        return $choices;
    }

    private function getLabelGroup(LevelType $type): string
    {
        return match($type) {
            LevelType::SCHOOL => 'Toute l\'école VTT',
            LevelType::FRAME => 'Tout l\'Encadrement',
            LevelType::ADULT => 'Tout les adultes',
        };
    }
}