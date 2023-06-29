<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\HealthQuestion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class HealthQuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
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
                'attr' => [
                    'class' => 'value',
                ],
                'constraints' => [
                    new NotNull(),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => HealthQuestion::class,
            'type' => 'adulte',
        ]);
    }
}
