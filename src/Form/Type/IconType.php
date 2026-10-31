<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\State\LucideIconProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IconType extends AbstractType
{
    public function __construct(
        private readonly LucideIconProvider $lucideIconProvider,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $choices = $this->lucideIconProvider->getIconChoices();

        $resolver->setDefaults([
            'choices' => $choices,
            'block_prefix' => 'ux_icon',
            'attr' => [
                'data-choices' => json_encode(array_values($choices)),
            ],
            'row_attr' => [
                'class' => 'not-last:border-border not-last:border-b not-last:pb-4',
            ],
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
