<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\SecondHandCategory;
use App\State\LucideIconProvider;
use Hoa\Compiler\Llk\Rule\Choice;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SecondHandCategoryType extends AbstractType
{
    public function __construct(
        private LucideIconProvider $lucideIconProvider,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $iconChoices = $this->lucideIconProvider->getIconChoices();

        $builder
            ->add('name', TextType::class, [
                'label' => 'Titre',
                'row_attr' => ['class' => 'form-group not-last:border-border not-last:border-b not-last:pb-4'],
            ])
            ->add('icon', ChoiceType::class, [
                'choices' => $iconChoices,
                'block_prefix' => 'ux_icon',
                'attr' => [
                    'data-choices' => array_values($iconChoices),
                ],
                'row_attr' => [
                    'class' => 'not-last:border-border not-last:border-b not-last:pb-4',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SecondHandCategory::class,
        ]);
    }
}
