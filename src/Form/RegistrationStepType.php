<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Licence;
use App\Entity\RegistrationStep;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationStepType extends AbstractType
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Catégorie',
                'placeholder' => 'Mineur et adulte',
                'choices' => array_flip(Licence::CATEGORIES),
                'required' => false,
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
            ])
            ->add('finalRender', ChoiceType::class, [
                'label' => 'Licence final',
                'choices' => array_flip(RegistrationStep::RENDERS),
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
            ])
            ->add('testingRender', ChoiceType::class, [
                'label' => '3 séances d\'essai',
                'choices' => array_flip(RegistrationStep::RENDERS),
                'row_attr' => [
                    'class' => 'form-group-inline',
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
                    'class' => 'form-group-inline',
                ],
            ])
            ->add('content', CKEditorType::class, [
                'label' => 'Contenu',
                'config_name' => 'full_config',
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RegistrationStep::class,
        ]);
    }
}
