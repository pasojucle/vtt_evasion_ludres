<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Product;
use App\Entity\Size;
use App\Validator\NotEmptyFile;
use Doctrine\ORM\EntityRepository;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'row_attr' => [
                    'class' => 'form-group',
                ],
            ])
            ->add('content', CKEditorType::class, [
                'label' => 'Détail (optionnel)',
                'config_name' => 'minimum_config',
                'required' => false,
            ])
            ->add('pictureFile', FileType::class, [
                'label' => 'Photo',
                'mapped' => false,
                'required' => false,
                'block_prefix' => 'custom_file',
                'attr' => [
                    'accept' => '.bmp,.jpeg,.jpg,.png',
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
                    ]),
                    new NotEmptyFile(),
                ],
            ])
            ->add('ref', TextType::class, [
                'label' => 'Référence',
                'row_attr' => [
                    'class' => 'form-group',
                ],
            ])
            ->add('price', NumberType::class, [
                'label' => 'Prix',
                'scale' => 2,
                'attr' => [
                    'min' => 0,
                ],
                'row_attr' => [
                    'class' => 'form-group',
                ],
            ])
            ->add('sizes', EntityType::class, [
                'label' => 'Tailles',
                'class' => Size::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('s')
                        ->orderBy('s.name', 'ASC')
                    ;
                },
                'choice_label' => 'name',
                'expanded' => true,
                'multiple' => true,
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
            'data_class' => Product::class,
        ]);
    }
}
