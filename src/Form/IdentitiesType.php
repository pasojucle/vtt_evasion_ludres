<?php

namespace App\Form;

use App\Entity\Licence;
use App\Form\IdentityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class IdentitiesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('identities', CollectionType::class, [
                'label' => false,
                'entry_type' => IdentityType::class,
                'entry_options' => [
                    'label' => false,
                    'category' => $options['category'],
                    'season_licence' => $options['season_licence'],
                    'is_kinship' => $options['is_kinship'],
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Modifier',
                'attr' => ['class' => 'btn btn-primary float-right'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'category' => Licence::CATEGORY_ADULT,
            'season_licence' => null,
            'is_kinship' => false,
        ]);
    }
}