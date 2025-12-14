<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Licence;
use App\Form\GardianType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class GardiansType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('userGardians', CollectionType::class, [
                'label' => false,
                'entry_type' => GardianType::class,
                'entry_options' => [
                    'label' => false,
                    'category' => $options['category'],
                    'is_yearly' => $options['is_yearly'],
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'category' => Licence::CATEGORY_ADULT,
            'is_yearly' => null,
        ]);
    }
}
