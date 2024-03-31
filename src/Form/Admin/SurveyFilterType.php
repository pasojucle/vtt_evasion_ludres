<?php

namespace App\Form\Admin;

use App\Entity\SurveyIssue;
use App\Entity\SurveyResponse;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SurveyFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('issue', EntityType::class, [
                'label' => 'Question',
                'class' => SurveyIssue::class,
                'choices' => $options['issues'],
                'choice_label' => 'content',
                'autocomplete' => true,

            ])
            ->add('value', ChoiceType::class, [
                'label' => 'Réponse',
                'choices' => array_flip(SurveyResponse::VALUES),
                'placeholder' => 'Toutes',
                'autocomplete' => true,
                'required' => false,

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
