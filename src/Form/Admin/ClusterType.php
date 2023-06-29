<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Cluster;
use App\Entity\Level;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ClusterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Nom du groupe',
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('maxUsers', IntegerType::class, [
                'label' => 'Nombre de personne par groupe hors encardrement (optionnel)',
                'required' => false,
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
            ])
            ->add('level', EntityType::class, [
                'label' => 'Niveau  (optionnel)',
                'class' => Level::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('l')
                        ->orderBy('l.type', 'ASC')
                        ->addOrderBy('l.title', 'ASC')
                    ;
                },
                'choice_label' => 'title',
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
                'required' => false,
            ])
            ->add('role', ChoiceType::class, [
                'label' => 'Role (optionnel)',
                'choices' => [
                    Cluster::CLUSTER_FRAME => 'ROLE_FRAME'
                ],
                'placeholder' => 'Aucun',
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => [
                    'class' => 'btn btn-primary float-right',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cluster::class,
        ]);
    }
}
