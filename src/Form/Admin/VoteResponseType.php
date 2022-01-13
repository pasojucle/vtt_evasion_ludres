<?php

namespace App\Form\Admin;

use App\Entity\VoteResponse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VoteResponseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('value', ChoiceType::class, [
                'choices' => array_flip(VoteResponse::VALUES),
                'label' => false,
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
            ])
            ->add('voteIssue', HiddenType::class)
            ->add('uder', HiddenType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => VoteResponse::class,
        ]);
    }
}
