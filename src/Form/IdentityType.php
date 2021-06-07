<?php

namespace App\Form;

use App\Entity\Licence;
use App\Entity\Identity;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class IdentityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $identity = $event->getData();
            $form = $event->getForm();
            $row_class =  ($options['is_kinship']) ? 'form-group-inline' : 'form-group';
            if ((!$options['is_kinship'] && $form->getName() === "0") || ($options['is_kinship'] && $form->getName() === "1")) {
                $form
                    ->add('name', TextType::class, [
                        'label' => 'Nom',
                        'row_attr' => [
                            'class' => $row_class
                        ],
                    ])
                    ->add('firstName', TextType::class, [
                        'label' => 'Prénom',
                        'row_attr' => [
                            'class' => $row_class,
                        ],
                    ])
                    ->add('phone', TextType::class, [
                        'label' => 'Téléphone fixe',
                        'required' => false,
                        'row_attr' => [
                            'class' => 'form-group-inline',
                        ],
                    ])
                    ->add('mobile', TextType::class, [
                        'label' => 'Téléphone mobile',
                        'row_attr' => [
                            'class' => 'form-group-inline',
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
                            'class' => $row_class,
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
                        'required' => false,
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
                        ->add('otherAddress', CheckboxType::class, [
                            'label' => 'Réside à une autre adresse que l\'enfant',
                            'required' => false,
                        ])
                        ;
                    $addressClass = ' hidden';
                } else {
                    $form
                        ->add('birthplace', TextType::class, [
                            'label' => 'Lieux de naissance',
                            'row_attr' => [
                                'class' => $row_class,
                            ],
                        ])
                        ->add('pictureFile', FileType::class, [
                            'label' => false,
                            'mapped' => false,
                            'required' => false,
                            'attr' => [
                                'accept' => '.bmp,.jpeg,.jpg,.png'
                            ],
                            'constraints' => [
                                new File([
                                    'maxSize' => '1024k',
                                    'mimeTypes' => [
                                        'image/bmp',
                                        'image/jpeg',
                                        'image/png',
                                    ],
                                    'mimeTypesMessage' => 'Format image bmp, jpeg ou png autorisé',
                                ])
                            ],
                        ])
                        ;
                    $addressClass = '';
                }
                $form->add('address', AddressType::class, [
                    'row_class' => $addressClass,
                ]);
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
