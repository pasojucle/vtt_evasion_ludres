<?php

namespace App\Form\Admin;

use App\Entity\VoteIssue;
use App\Entity\VoteResponse;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SurveyFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('issue', EntityType::class, [
                'label' => 'Question',
                'class' => VoteIssue::class,
                'choices' => $options['issues'],
                'choice_label' => 'content',
                'expanded' => false,
                'multiple' => false,
                'attr' => [
                    'class' => 'btn',
                ],
            ])
            ->add('value', ChoiceType::class, [
                'label' => 'Réponse',
                'choices' => array_flip(VoteResponse::VALUES),
                'placeholder' => 'Toutes',
                'required' => false,
                'attr' => [
                    'class' => 'btn',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => '<i class="fas fa-search"></i>',
                'label_html' => true,
                'attr' => [
                    'class' => 'btn btn-ico',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'issues' => [],
        ]);
    }
}
