<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Level;
use App\Entity\Cluster;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class SkillFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('level', EntityType::class, [
                'label' => 'Niveau',
                'class' => Level::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('l')
                        ->andWhere(
                            (new Expr)->eq('l.type', ':type'),
                            (new Expr)->eq('l.isDeleted', ':deleted'),
                        )
                        ->setParameter('type', Level::TYPE_SCHOOL_MEMBER)
                        ->setParameter('deleted', false)
                        ->orderBy('l.title', 'ASC')
                    ;
                },
                'choice_label' => 'title',
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
                'autocomplete' => true,
                'required' => false,
            ])
        ;
    }
}
