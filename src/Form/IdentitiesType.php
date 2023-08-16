<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Licence;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IdentitiesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('identities', CollectionType::class, [
                'label' => false,
                'entry_type' => IdentityType::class,
                'entry_options' => [
                    'label' => false,
                    'category' => $options['category'],
                    'is_final' => $options['is_final'],
                    'is_kinship' => $options['is_kinship'],
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Modifier',
                'attr' => [
                    'class' => 'btn btn-primary float-right',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'category' => Licence::CATEGORY_ADULT,
            'is_final' => null,
            'is_kinship' => false,
        ]);
    }
}
