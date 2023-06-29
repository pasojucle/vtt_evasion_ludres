<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Licence;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('licenceNumber', TextType::class, [
                'label' => 'numéro de licence',
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
            ])
            ->add('licences', CollectionType::class, [
                'label' => false,
                'entry_type' => LicenceType::class,
                'entry_options' => [
                    'label' => false,
                    'season_licence' => $options['season_licence'],
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => '<i class="fas fa-check"></i> Modifier',
                'label_html' => true,
                'attr' => [
                    'class' => 'btn btn-primary float-right',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'category' => Licence::CATEGORY_ADULT,
            'season_licence' => null,
        ]);
    }
}
