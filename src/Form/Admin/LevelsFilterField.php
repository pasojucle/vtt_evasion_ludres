<?php

namespace App\Form\Admin;

use App\Entity\Enum\LevelType;
use App\Entity\Level;
use App\Repository\LevelRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class LevelsFilterField extends AbstractType
{
    public function __construct(
        private TranslatorInterface $translator,
        private LevelRepository $levelRepository,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'multiple' => true,
            'choices' => $this->getLevelChoices(),
            'required' => false,
            'autocomplete' => true,
            'attr' => [
                'data-width' => '100%',
                'data-placeholder' => 'Sélectionnez un ou plusieurs niveaux',
                'data-action' => 'change->filter#submit'
            ],
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
    public function getLevelChoices(): array
    {
        $levelChoices = [];

        $this->addLevelTypes($levelChoices);

        $this->addLevels($levelChoices);

        $levelChoices['Membres du bureau et comité'] = Level::TYPE_BOARD_MEMBER;

        return $levelChoices;
    }

    private function addLevels(array &$choices): void
    {
        foreach ($this->levelRepository->findAll() as $level) {
            $choices[$level->getType()->trans($this->translator)][$level->getTitle()] = $level->getId();
        }
    }

    private function addLevelTypes(array &$choices): void
    {
        foreach (LevelType::cases() as $levelType) {
            $choices[$levelType->trans($this->translator)] = [$levelType->trans($this->translator) => $levelType->value];
        }
    }
}
