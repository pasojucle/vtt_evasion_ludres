<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\SurveyIssue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SurveyIssueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextType::class, [
                'label' => false,
                'row_attr' => [
                    'class' => 'col-md-7 form-group-inline',
                ],
            ])
            ->add('responseType', ChoiceType::class, [
                'label' => false,
                'choices' => array_flip(SurveyIssue::RESPONSE_TYPES),
                'row_attr' => [
                    'class' => 'col-md-4 form-group-inline',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SurveyIssue::class,
        ]);
    }
}
