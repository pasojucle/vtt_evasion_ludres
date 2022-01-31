<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Level;
use App\Entity\Licence;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
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
            ->add('level', EntityType::class, [
                'label' => 'Niveau',
                'class' => Level::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('l')
                        ->addOrderBy('l.type', 'ASC')
                        ->addOrderBy('l.orderBy', 'ASC')
                    ;
                },
                'group_by' => function ($choice, $key, $value) {
                    if (Level::TYPE_MEMBER === $choice->getType()) {
                        return 'Adhérent';
                    }

                    return 'Encadrement';
                },
                'placeholder' => 'Aucun',
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
                'required' => false,
            ])
            ->add('health', HealthType::class, [
                'label' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Modifier',
                'attr' => [
                    'class' => 'btn btn-primary float-right',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'category' => Licence::CATEGORY_ADULT,
            'season_licence' => null,
        ]);
    }
}
