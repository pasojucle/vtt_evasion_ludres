<?php

namespace App\Form;

use App\Entity\HealthQuestion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class HealthQuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('value', ChoiceType::class, [
                'label' => false,
                'choices' => [
                    'oui' => true,
                    'non' => false,
                ],
                'expanded' => true,
                'multiple' => false,
                'attr' => ['class' => 'value'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => HealthQuestion::class,
            'type' => 'adulte',
        ]);
    }
}