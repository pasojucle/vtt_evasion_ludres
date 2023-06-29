<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Documentation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class DocumentationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Titre',
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
            ])
            ->add('file', FileType::class, [
                'label' => 'Fichier',
                'mapped' => false,
                'required' => false,
                'block_prefix' => 'custom_file',
                'attr' => [
                    'accept' => '.bmp,.jpeg,.jpg,.png, .pdf',
                ],
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '8196k',
                        'mimeTypes' => [
                            'image/bmp',
                            'image/jpeg',
                            'image/png',
                            'application/pdf',
                        ],
                        'mimeTypesMessage' => 'Format image bmp, jpeg, png ou pdf autorisé',
                    ]),
                ],
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
            'data_class' => Documentation::class,
        ]);
    }
}
