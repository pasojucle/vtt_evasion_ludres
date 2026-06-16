<?php

declare(strict_types=1);

namespace App\Form;

use App\Form\Transformer\HiddenChoiceMultipleTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HiddenChoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (true === $options['multiple']) {
            $builder->addViewTransformer(new HiddenChoiceMultipleTransformer());
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('multiple', false);
        $resolver->setAllowedTypes('multiple', 'bool');
        
        $resolver->setDefault('choices', []);
        $resolver->setAllowedTypes('choices', 'array');
    }

    public function getParent(): string
    {
        return HiddenType::class;
    }
}
