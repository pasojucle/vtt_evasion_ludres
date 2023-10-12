<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\SecondHand;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class SecondHandType extends AbstractType
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
    ) {
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('isAgree', CheckboxType::class, [
                'label' => 'J\'accèpte les conditions d\'utilisation',
                'attr' => [
                    'class' => 'form-group check-toggle',
                ],
                'mapped' => false,
            ])
            ->add('name', TextType::class, [
                    'label' => 'Titre',
                    'row_attr' => [
                        'class' => 'form-group',
                    ],
                ])
            ->add('content', TextareaType::class, [
                'label' => 'Descriptif',
                'row_attr' => [
                    'class' => 'form-group',
                ],
                'attr' => [
                    'class' => 'second-hand',
                ],
            ])
            ->add('filename', FileType::class, [
                'label' => 'Télecharger une photo ',
                'mapped' => false,
                'required' => false,
                'block_prefix' => 'custom_file',
                'attr' => [
                    'accept' => '.bmp,.jpeg,.jpg,.png',
                ],
                'row_attr' => [
                    'class' => 'second-hand',
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '2048k',
                        'mimeTypes' => [
                            'image/bmp',
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Format image bmp, jpeg, png ou pdf autorisé',
                    ]),
                ],
            ])
            ->add('category', EntityType::class, [
                'label' => 'Categorie',
                'class' => Category::class,
                'choices' => $this->categoryRepository->findAllAsc(),
                'choice_label' => 'name',
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
            'data_class' => SecondHand::class,
        ]);
    }
}
