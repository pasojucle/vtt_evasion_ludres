<?php

namespace App\Form\Admin;

use App\Entity\Notification;
use App\Form\Type\TiptapType;
use App\Validator\Period;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NotificationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
            ])
            ->add('content', TiptapType::class, [
                'label' => 'Contenu',
                'config_name' => 'full',
                'required' => false,
            ])
            ->add('startAt', DateType::class, [
                'input' => 'datetime_immutable',
                'label' => 'Date de départ',
                'attr' => [
                    'autocomplete' => 'off',
                ],
            ])
            ->add('endAt', DateType::class, [
                'input' => 'datetime_immutable',
                'label' => 'Date de fin',
                'attr' => [
                    'autocomplete' => 'off',
                ],
                'constraints' => [new Period()],
            ])
            ->add('minAge', IntegerType::class, [
                'label' => 'Age minimum (optionnel)',
                'attr' => [
                    'min' => 0,
                    'max' => 90,
                ],
                'required' => false,
            ])
            ->add('maxAge', IntegerType::class, [
                'label' => 'Age maximum (optionnel)',
                'attr' => [
                    'min' => 0,
                    'max' => 90,
                ],
                'required' => false,
            ])
            ->add('public', CheckboxType::class, [
                'block_prefix' => 'switch',
                'attr' => [
                    'data-switch-on' => 'Mode public activé',
                    'data-switch-off' => 'Afficher uniquement aux utilisateurs connectés',
                ],
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Notification::class,
        ]);
    }
}
