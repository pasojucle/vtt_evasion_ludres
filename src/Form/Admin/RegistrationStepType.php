<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Enum\DisplayModeEnum;
use App\Entity\Enum\LicenceCategoryEnum;
use App\Entity\Enum\RegistrationFormEnum;
use App\Entity\RegistrationStep;
use App\Entity\RegistrationStepGroup;
use App\Form\Type\TiptapType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class RegistrationStepType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'row_attr' => [
                    'class' => 'form-group',
                ],
            ])
            ->add('registrationStepGroup', EntityType::class, [
                'label' => 'Groupe',
                'class' => RegistrationStepGroup::class,
                'choice_label' => 'title',
                'row_attr' => [
                    'class' => 'form-group',
                ],
            ])
            ->add('category', EnumType::class, [
                'label' => 'Catégorie',
                'class' => LicenceCategoryEnum::class,
                'placeholder' => 'licence.category.place_holder',
                'required' => false,
                'row_attr' => [
                    'class' => 'form-group',
                ],
            ])
            ->add('yearlyDisplayMode', EnumType::class, [
                'label' => 'Licence final',
                'class' => DisplayModeEnum::class,
                'row_attr' => [
                    'class' => 'form-group',
                ],
            ])
            ->add('trialDisplayMode', EnumType::class, [
                'label' => '3 séances d\'essai',
                'class' => DisplayModeEnum::class,
                'row_attr' => [
                    'class' => 'form-group',
                ],
            ])
            ->add('pdfFile', FileType::class, [
                'label' => 'Fichier pdf',
                'mapped' => false,
                'required' => false,
                'block_prefix' => 'custom_file',
                'attr' => [
                    'accept' => '.pdf',
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'application/pdf',
                        ],
                        'mimeTypesMessage' => 'Format pdf obligatoire',
                    ]),
                ],
            ])
            ->add('form', EnumType::class, [
                'label' => 'Nom du formulaire',
                'placeholder' => 'Aucun',
                'class' => RegistrationFormEnum::class,
                'row_attr' => [
                    'class' => 'form-group',
                ],
            ])
            ->add('content', TiptapType::class, [
                'label' => 'Contenu',
                'config_name' => 'full',
                'required' => false,
            ])
            ->add('personal', CheckboxType::class, [
                'block_prefix' => 'switch',
                'attr' => [
                    'data-switch-off' => 'À joindre au dossier d\'inscription',
                    'data-switch-on' => 'À conserver par l\'adhérent',
                ],
                'required' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => [
                    'class' => 'btn btn-primary',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RegistrationStep::class,
        ]);
    }
}
