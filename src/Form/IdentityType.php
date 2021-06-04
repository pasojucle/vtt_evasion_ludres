<?php

namespace App\Form;

use App\Entity\Identity;
use App\Entity\Licence;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class IdentityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $identity = $event->getData();
            $form = $event->getForm();
            if ((!$options['is_kinship'] && $form->getName() === "0") || ($options['is_kinship'] && $form->getName() === "1")) {
                $form
                    ->add('name', TextType::class, [
                        'label' => 'Nom',
                        'row_attr' => [
                            'class' => 'form-group-inline'
                        ],
                    ])
                    ->add('firstName', TextType::class, [
                        'label' => 'Prénom',
                        'row_attr' => [
                            'class' => 'form-group-inline'
                        ],
                    ])
                    ->add('phone', TextType::class, [
                        'label' => 'Téléphone fixe',
                        'required' => false,
                        'row_attr' => [
                            'class' => 'form-group-inline'
                        ],
                    ])
                    ->add('mobile', TextType::class, [
                        'label' => 'Téléphone mobile',
                        'row_attr' => [
                            'class' => 'form-group-inline'
                        ],
                    ])
                    ->add('email', EmailType::class, [
                        'label' => 'Adresse mail',
                        'row_attr' => [
                            'class' => 'form-group-inline'
                        ],
                    ])
                    ->add('birthDate', DateTimeType::class, [
                        'label' => 'Date de naissance',
                        'widget' => 'single_text',
                        'html5' => false,
                        'format' => 'dd/MM/yyyy',
                        'attr' => [
                            'class' => 'js-datepicker',
                            'autocomplete' => "off",
                        ],
                        'row_attr' => [
                            'class' => 'form-group-inline'
                        ],
                    ])
                ;
                if (Licence::CATEGORY_ADULT === $options['category']) {
                    $form
                    ->add('profession', TextType::class, [
                        'label' => 'Profession',
                        'row_attr' => [
                            'class' => 'form-group-inline'
                        ],
                    ])
                    ;
                }
                
                if ($options['is_kinship']) {
                    $form
                        ->add('kinship', ChoiceType::class, [
                            'label' => 'Parenté',
                            'choices' => array_flip(Identity::KINSHIPS),
                            'row_attr' => [
                                'class' => 'form-group-inline'
                            ],
                        ])
                        ;
                } else {
                    $form
                        ->add('birthplace', TextType::class, [
                            'label' => 'Lieux de naissance',
                            'row_attr' => [
                                'class' => 'form-group-inline'
                            ],
                        ])
                        ;
                }
            }
            dump($form);
        });

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Identity::class,
            'is_kinship' => false,
            'current' => null,
            'category' => Licence::CATEGORY_ADULT,
        ]);
    }
}
