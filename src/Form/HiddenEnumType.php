<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Enum\PracticeEnum;
use App\Form\Transformer\HiddenEnumMultipleTransformer;
use App\Form\Transformer\HiddenEnumTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Enum hidden custom type class definition.
 */
class HiddenEnumType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (true === $options['multiple']) {
            // CRUCIAL : On utiliseaddViewTransformer pour le cas multiple.
            // Cela garantit que la donnée finale envoyée au template Twig reste une string (JSON).
            $builder->addViewTransformer(new HiddenEnumMultipleTransformer($options['class']));
        } else {
            $builder->addModelTransformer(new HiddenEnumTransformer($options['class']));
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('class');
        $resolver->setDefault('multiple', false);   
        $resolver->setAllowedTypes('class', 'string');
        $resolver->setAllowedTypes('multiple', 'bool');    
    }

    public function getParent(): string
    {
        // On déclare proprement que notre champ se base sur un HiddenType
        return HiddenType::class;
    }
}
