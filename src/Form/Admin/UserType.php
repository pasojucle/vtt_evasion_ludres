<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\User;
use App\Entity\Level;
use App\Entity\Licence;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class UserType extends AbstractType
{
    public function __construct(private Security $security)
    {
        
    }
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
                    if (Level::TYPE_SCHOOL_MEMBER === $choice->getType()) {
                        return 'Adhérent';
                    }

                    return 'Encadrement';
                },
                'placeholder' => 'Aucun',
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
                'attr' => [
                    'class' => 'form-modifier',
                    'data-modifier' => 'framer_container',
                ],
                'required' => false,
            ])

            ->add('health', HealthType::class, [
                'label' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => '<i class="fas fa-check"></i> Modifier',
                'label_html' => true,
                'attr' => [
                    'class' => 'btn btn-primary float-right',
                ],
            ])
        ;

        $formModifier = function(FormInterface $form, ?Level $level, User $user) {
            if ($this->security->isGranted('ROLE_ADMIN') && Level::TYPE_FRAME === $level?->getType()) {
                $form
                    ->add('isFramer', CheckboxType::class, [
                        'block_prefix' => 'switch',
                        'required' => false,
                        'row_attr' => [
                            'class' => 'form-group-inline',
                        ],
                        'attr' => [
                            'data-switch-on' => 'Donner les accès admin pour les sortie',
                            'data-switch-off' => 'Aucun accès admin',
                        ],
                        'data' => in_array('ROLE_FRAME', $user->getRoles()),
                        'mapped' => false,
                    ])
                ;
            }
        };

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event)  use ($formModifier)  {
            $form = $event->getForm();
            $data = $event->getData();

            $formModifier($form, $data->getLevel(), $data);
            
        });


        $builder->get('level')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $level = $event->getForm()->getData();
                $user = $event->getForm()->getParent()->getData();
                $formModifier($event->getForm()->getParent(), $level, $user);
            }
        );
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
