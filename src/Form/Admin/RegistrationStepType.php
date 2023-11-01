<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Form\UserType;
use App\Entity\Licence;
use App\Entity\RegistrationStep;
use Symfony\Component\Form\AbstractType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class RegistrationStepType extends AbstractType
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'row_attr' => [
                    'class' => 'form-group',
                ],
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Catégorie',
                'placeholder' => 'licence.category.place_holder',
                'choices' => array_flip(Licence::CATEGORIES),
                'required' => false,
                'row_attr' => [
                    'class' => 'form-group',
                ],
            ])
            ->add('finalRender', ChoiceType::class, [
                'label' => 'Licence final',
                'choices' => array_flip(RegistrationStep::RENDERS),
                'row_attr' => [
                    'class' => 'form-group',
                ],
            ])
            ->add('testingRender', ChoiceType::class, [
                'label' => '3 séances d\'essai',
                'choices' => array_flip(RegistrationStep::RENDERS),
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
            ->add('form', ChoiceType::class, [
                'label' => 'Nom du formulaire',
                'placeholder' => 'Aucun',
                'choices' => array_flip(UserType::FORMS),
                'choice_label' => function ($choice, $key, $value) {
                    return $this->translator->trans($key);
                },
                'required' => false,
                'row_attr' => [
                    'class' => 'form-group',
                ],
            ])
            ->add('content', CKEditorType::class, [
                'label' => 'Contenu',
                'config_name' => 'full_config',
                'required' => false,
            ])
            ->add('personal', CheckboxType::class, [
                'block_prefix' => 'switch',
                'attr' => [
                    'data-switch-off' => 'À joindre au dossier d\'inscription',
                    'data-switch-on' => 'À concerver par l\'adhérent',
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
