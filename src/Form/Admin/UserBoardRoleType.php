<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\BoardRole;
use App\Entity\Level;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

class UserBoardRoleType extends AbstractType
{
    public function __construct(
        private Security $security,
        private AccessDecisionManagerInterface $accessDecisionManager
    ) {
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

        $formModifier = function (FormInterface $form, User $user) {
            $token = new UsernamePasswordToken($user, 'none', $user->getRoles());
            $isGrantedAdmin = $this->accessDecisionManager->decide($token, ['ROLE_ADMIN']);
            if (!$isGrantedAdmin && $this->security->isGranted('PERMISSION_EDIT', $user)) {
                $form
                    ->add('permissions', CollectionType::class, [
                        'entry_type' => CheckboxType::class,
                        'entry_options' => [
                            'block_prefix' => 'switch_permission',
                            'required' => false,
                        ]
                    ])
                ;
            }
        };

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($formModifier) {
            $form = $event->getForm();
            $data = $event->getData();

            $formModifier($form, $data);
        });


        $builder->get('level')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $user = $event->getForm()->getParent()->getData();
                $formModifier($event->getForm()->getParent(), $user);
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
