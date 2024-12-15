<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\SecondHand;
use App\Form\SecondHandImageType;
use App\Repository\CategoryRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
                'label' => 'J\'accepte les conditions d\'utilisation',
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
            ->add('images', CollectionType::class, [
                'label' => false,
                'entry_type' => SecondHandImageType::class,
                'entry_options' => [
                    'label' => false,
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
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
            ->add('price', MoneyType::class, [
                'label' => 'Prix',
                'divisor' => 100,
                'attr' => [
                    'min' => 0,
                ],
                'row_attr' => [
                    'class' => 'form-group form-money',
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
