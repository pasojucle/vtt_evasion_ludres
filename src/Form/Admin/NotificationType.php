<?php

namespace App\Form\Admin;

use App\Entity\Notification;
use App\Form\Type\CkeditorType;
use App\Validator\Period;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
            ])
            ->add('content', CKEditorType::class, [
                'label' => 'Contenu',
                'config_name' => 'full',
                'required' => false,
            ])
            ->add('startAt', DateTimeType::class, [
                'input' => 'datetime_immutable',
                'label' => 'Date de départ',
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => [
                    'class' => 'js-datepicker',
                    'autocomplete' => 'off',
                ],
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
            ])
            ->add('endAt', DateTimeType::class, [
                'input' => 'datetime_immutable',
                'label' => 'Date de fin',
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => [
                    'class' => 'js-datepicker',
                    'autocomplete' => 'off',
                ],
                'row_attr' => [
                    'class' => 'form-group-inline',
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
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
            ])
            ->add('maxAge', IntegerType::class, [
                'label' => 'Age maximum (optionnel)',
                'attr' => [
                    'min' => 0,
                    'max' => 90,
                ],
                'required' => false,
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
            ])
            ->add('public', CheckboxType::class, [
                'block_prefix' => 'switch',
                'attr' => [
                    'data-switch-on' => 'Mode public activé',
                    'data-switch-off' => 'Afficher uniquement aux utilisateurs connectés',
                ],
                'required' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => [
                    'class' => 'btn btn-primary float-right',
                ],
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
