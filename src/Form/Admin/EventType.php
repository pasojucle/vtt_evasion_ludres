<?php

namespace App\Form\Admin;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'row_attr' => [
                    'class' => 'form-group',
                ],
            ])
            ->add('content', CKEditorType::class, [
                'label' => 'Détail (optionnel)',
                'config_name' => 'minimum_config',
                'required' => false,
            ])
            ->add('startAt', DateTimeType::class, [
                'label' => 'Date de départ',
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => [
                    'class' => 'js-datepicker',
                    'autocomplete' => "off",
                ],
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
            ])
            ->add('endAt', DateTimeType::class, [
                'label' => 'Date de fin (optionnel)',
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => [
                    'class' => 'js-datepicker',
                    'autocomplete' => "off",
                ],
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
                'required' => false,
            ])
            ->add('displayDuration', IntegerType::class, [
                'label' => 'Durée d\'affichage (nbr de jours avant)',
                'required' => false,
                'attr' => [
                    'min' => 0,
                    'max' => 90,
                ],
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
            ])
            ->add('closingDuration', IntegerType::class, [
                'label' => 'Fin d\'inscription (nbr de jours avant)',
                'required' => false,
                'attr' => [
                    'min' => 0,
                    'max' => 90,
                ],
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
            ])
            ->add('minAge', IntegerType::class, [
                'label' => 'Age minimum (optionnel)',
                'attr' => [
                    'min' => 10,
                    'max' => 90,
                ],
                'required' => false,
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type de randonnée',
                'choices' => array_flip(Event::TYPES),
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregister',
                'attr' => ['class' => 'btn btn-primary float-right'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
