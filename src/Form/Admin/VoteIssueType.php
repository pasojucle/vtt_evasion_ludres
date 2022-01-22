<?php

namespace App\Form\Admin;

use App\Entity\VoteIssue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class VoteIssueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('content', TextType::class, [
                'label' => false,
                'row_attr' => [
                    'class' => 'col-md-7 form-group ',
                ],
            ])
            ->add('responseType', ChoiceType::class, [
                'label' => false,
                'choices' => array_flip(VoteIssue::RESPONSE_TYPES),
                'row_attr' => [
                    'class' => 'col-md-4 form-group ',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => VoteIssue::class,
        ]);
    }
}
