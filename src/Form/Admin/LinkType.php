<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Link;
use App\Form\Type\TiptapType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class LinkType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('url', UrlType::class, [
                'label' => 'Url du lien',
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
            ])

            ->add('title', TextType::class, [
                'label' => 'Titre',
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'row_attr' => [
                    'class' => 'form-group',
                ],
                'required' => false,
            ])
            ->add('content', TiptapType::class, [
                'label' => 'Détail (optionnel)',
                'config_name' => 'full',
                'required' => false,
                'row_attr' => [
                    'class' => 'form-group',
                ],
            ])
            ->add('image', TextType::class, [
                'label' => 'Url ou nom de l\'image',
                'required' => false,
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
            ])
            ->add('imageFile', FileType::class, [
                'label' => false,
                'mapped' => false,
                'required' => false,
                'block_prefix' => 'custom_file',
                'attr' => [
                    'accept' => '.bmp,.jpeg,.jpg,.png,.gif,.svg',
                ],
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/bmp',
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                            'image/svg+xml',
                        ],
                        'mimeTypesMessage' => 'Format image bmp, jpeg ou png autorisé',
                    ]),
                ],
            ])
            ->add('position', ChoiceType::class, [
                'label' => 'Position',
                'choices' => array_flip(Link::POSITIONS),
                'row_attr' => [
                    'class' => 'form-group-inline',
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
            'data_class' => Link::class,
        ]);
    }
}
