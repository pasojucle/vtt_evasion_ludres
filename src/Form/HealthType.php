<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\DiseaseKind;
use App\Entity\Health;
use App\Validator\Phone;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class HealthType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (null !== $options['current'] && UserType::FORM_HEALTH === $options['current']->getForm()) {
            $builder
                ->add('content', TextareaType::class, [
                    'label' => false,
                    'attr' => [
                        'class' => 'textarea',
                    ],
                    'required' => false,
                ])
            ;
        }

        if (null !== $options['current'] && UserType::FORM_HEALTH_QUESTION === $options['current']->getForm()) {
            $builder
                ->add('healthQuestions', CollectionType::class, [
                    'label' => false,
                    'entry_type' => HealthQuestionType::class,
                ])
            ;
        }

        if (null === $options['current']) {
            $builder
                ->add('save', SubmitType::class, [
                    'label' => 'Modifier',
                    'attr' => [
                        'class' => 'btn btn-primary float-right',
                    ],
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Health::class,
            'current' => null,
        ]);
    }
}
