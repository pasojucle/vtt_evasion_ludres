<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Enum\PracticeEnum;
use App\Form\Transformer\HiddenEnumTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Enum hidden custom type class definition.
 */
class HiddenEnumType extends HiddenType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder->addModelTransformer(new HiddenEnumTransformer($options['class']));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('class');
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
