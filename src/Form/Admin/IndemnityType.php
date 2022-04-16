<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Level;
use App\Entity\BikeRide;
use App\Entity\Indemnity;
use App\Form\HiddenLevelType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class IndemnityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $indemnity = $event->getData();
            $form = $event->getForm();
            if (null === $indemnity) {
                $form
                    ->add('level', EntityType::class, [
                        'class' => Level::class,
                        'choice_label' => 'title',
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('l')
                            ->addOrderBy('l.type', 'ASC')
                            ->addOrderBy('l.title', 'ASC')
                            ;
                        }
                    ])
                    ->add('bikeRideType', EntityType::class, [
                        'label' => 'Type de randonnée',
                        'class' => BikeRide::class,
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('brt')
                                ->orderBy('brt.title', 'ASC')
                            ;
                        },
                        'choice_label' => 'title',
                    ])
                    ;
            } else {
                $form
                    ->add('level', HiddenLevelType::class)
                    ->add('bikeRideType', HiddenType::class)
                    ;
            }
        });

        $builder
            ->add('amount', NumberType::class, [
                'label' => 'Montant',
                'scale' => 2,
                'attr' => [
                    'class' => 'align-right',
                ],
                'row_attr' => [
                    'class' => 'form-group',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Indemnity::class,
        ]);
    }
}
