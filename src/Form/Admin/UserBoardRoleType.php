<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\User;
use App\Entity\Level;
use App\Entity\Licence;
use App\Entity\BoardRole;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

class UserBoardRoleType extends AbstractType
{
    public function __construct(private Security $security, private RoleHierarchyInterface $roleHierarchy)
    {
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('level', EntityType::class, [
                'label' => 'Niveau',
                'class' => Level::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('l')
                        ->andWhere(
                            (new Expr())->eq('l.isDeleted', ':isDeleted'),
                        )
                        ->setParameter('isDeleted', false)
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
            ->add('boardRole', EntityType::class, [
                'label' => 'Fonction',
                'class' => BoardRole::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('br')
                        ->addOrderBy('br.orderBy', 'ASC')
                    ;
                },
                'placeholder' => 'Aucun',
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
                'required' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => '<i class="fas fa-check"></i> Modifier',
                'label_html' => true,
                'attr' => [
                    'class' => 'btn btn-primary float-right',
                ],
            ])
        ;

        $formModifier = function (FormInterface $form, ?Level $level, User $user) {
            if ($this->security->isGranted('ROLE_ADMIN') && Level::TYPE_FRAME === $level?->getType()) {
                $reachableRoles = $this->roleHierarchy->getReachableRoleNames($user->getRoles());

                $form
                    ->add('isFramer', CheckboxType::class, [
                        'block_prefix' => 'switch',
                        'required' => false,
                        'row_attr' => [
                            'class' => 'form-group-inline',
                        ],
                        'attr' => [
                            'data-switch-on' => 'Donner les accès admin pour les sorties',
                            'data-switch-off' => 'Aucun accès admin',
                        ],
                        'data' => in_array('ROLE_FRAME', $reachableRoles),
                        'disabled' => in_array('ROLE_ADMIN', $reachableRoles),
                        'mapped' => false,
                    ])
                ;
            }
        };

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($formModifier) {
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
